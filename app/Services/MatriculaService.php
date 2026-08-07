<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class MatriculaService
{
    public function __construct(
        private readonly CursoInscricaoService $inscricaoService = new CursoInscricaoService(),
        private readonly AlunoService $alunoService = new AlunoService(),
        private readonly TurmaService $turmaService = new TurmaService(),
        private readonly \App\Repositories\MatriculaRepository $matriculaRepository = new \App\Repositories\MatriculaRepository(),
        private readonly EmailService $emailService = new EmailService(),
    ) {
    }

    /**
     * @param array<string, mixed> $payment
     * @return array<string, mixed>
     */
    public function confirmarPagamento(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            throw new RuntimeException('Payment ID vazio');
        }

        $inscricao = $this->inscricaoService->findByAsaasPayment($paymentId);
        if (!$inscricao) {
            throw new RuntimeException('Inscrição não encontrada');
        }

        $idInscricao = (int) ($inscricao['id'] ?? 0);
        if ($idInscricao <= 0) {
            throw new RuntimeException('Inscrição inválida');
        }

        $matriculaExistente = $this->matriculaRepository->findByPagamento($idInscricao);
        if ($matriculaExistente !== null) {
            return [
                'message' => 'Pagamento já processado anteriormente',
                'inscricaoId' => $idInscricao,
                'alunoId' => (int) ($inscricao['id_aluno'] ?? 0),
                'matriculaId' => (int) ($matriculaExistente['id'] ?? 0),
                'numeroMatricula' => (string) ($matriculaExistente['numero'] ?? ''),
            ];
        }

        $this->inscricaoService->atualizarStatus($idInscricao, 'RECEBIDO');

        try {
            $cpf = preg_replace('/\D/', '', (string) ($inscricao['cpf'] ?? ''));
            $nome = (string) ($inscricao['nome'] ?? '');
            $email = (string) ($inscricao['email'] ?? '');
            $telefone = (string) ($inscricao['telefone'] ?? '');
            $idCurso = (int) ($inscricao['id_curso'] ?? 0);

            if ($cpf === '' || $nome === '' || $idCurso <= 0) {
                throw new RuntimeException('Dados insuficientes para matrícula');
            }

            $aluno = $this->alunoService->findByCpf($cpf);
            if ($aluno) {
                $idAluno = (int) ($aluno['id'] ?? 0);
            } else {
                $dataNascimento = (string) ($inscricao['data_nascimento'] ?? '2000-01-01');
                if ($dataNascimento === '') {
                    $dataNascimento = '2000-01-01';
                }
                $idAluno = $this->alunoService->criarAluno($nome, $cpf, $dataNascimento, $telefone, $email);
                if ($idAluno <= 0) {
                    throw new RuntimeException('Falha ao criar aluno');
                }
            }

            $turmas = $this->turmaService->turmasDoCurso($idCurso);
            if ($turmas === []) {
                throw new RuntimeException('Nenhuma turma ativa encontrada para o curso');
            }

            $idTurmaInscricao = (int) ($inscricao['id_turma'] ?? 0);
            if ($idTurmaInscricao > 0) {
                $turma = $this->turmaService->findTurma($idTurmaInscricao);
                if ($turma === null || intval($turma['ativo'] ?? 0) !== 1) {
                    $turma = $this->selecionarTurma($turmas);
                }
            } else {
                $turma = $this->selecionarTurma($turmas);
            }

            $idTurma = (int) ($turma['id'] ?? 0);
            if ($idTurma <= 0) {
                throw new RuntimeException('Turma inválida');
            }

            $idMatricula = $this->criarMatricula($idAluno, $idCurso, $idTurma, $idInscricao);

            $this->inscricaoService->atualizarStatus($idInscricao, 'CONFIRMADO', $idAluno, $idMatricula);

            $this->enviarBoasVindas($idAluno, $nome, $email, $cpf, $idMatricula);

            return [
                'message' => 'Matrícula realizada com sucesso',
                'inscricaoId' => $idInscricao,
                'alunoId' => $idAluno,
                'matriculaId' => $idMatricula,
            ];
        } catch (\Throwable $e) {
            $this->inscricaoService->atualizarStatus($idInscricao, 'CANCELADO');
            throw $e instanceof RuntimeException ? $e : new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Cria a matrícula na tabela "matricula" com número próprio.
     */
    private function criarMatricula(int $idAluno, int $idCurso, int $idTurma, int $idPagamento): int
    {
        $ano = (int) date('Y');
        $numero = (string) $this->matriculaRepository->proximoNumero($ano);

        $idMatricula = $this->matriculaRepository->create([
            'numero' => $numero,
            'id_aluno' => $idAluno,
            'id_curso' => $idCurso,
            'id_turma' => $idTurma,
            'id_pagamento' => $idPagamento,
            'origem' => 'SITE',
            'status' => 'ATIVA',
            'data_matricula' => date('Y-m-d H:i:s'),
            'ativo' => 1,
        ]);

        if ($idMatricula <= 0) {
            throw new RuntimeException('Falha ao criar matrícula');
        }

        return $idMatricula;
    }

    private function enviarBoasVindas(int $idAluno, string $nome, string $email, string $cpf, int $idMatricula): void
    {
        if ($email === '') {
            return;
        }

        try {
            $senha = explode('@', $email)[0] . '#' . date('Y');

            $linkRedefinicao = '';
            if ($idAluno > 0) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->alunoService->salvarResetToken($idAluno, $token, $expires);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = (string) ($_SERVER['HTTP_HOST'] ?? 'inteligenciaeducacionalsouzabrazil.com');
                $linkRedefinicao = "{$scheme}://{$host}/aluno/redefinir-senha?token={$token}";
            }

            $this->emailService->enviarBoasVindasMatricula($nome, $email, $cpf, $senha, (string) $idMatricula, $linkRedefinicao);
        } catch (\Throwable $e) {
            error_log('[MATRICULA] Erro ao enviar boas-vindas: ' . $e->getMessage());
        }
    }

    /**
     * @param array<string, mixed> $payment
     * @return array<string, mixed>
     */
    public function atualizarPagamento(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            throw new RuntimeException('Payment ID vazio');
        }

        $inscricao = $this->inscricaoService->findByAsaasPayment($paymentId);
        if (!$inscricao) {
            throw new RuntimeException('Inscrição não encontrada');
        }

        $statusMap = [
            'RECEIVED' => 'RECEBIDO',
            'CONFIRMED' => 'CONFIRMADO',
            'OVERDUE' => 'CANCELADO',
            'REFUNDED' => 'ESTORNADO',
            'CANCELED' => 'CANCELADO',
            'RECEIVED_IN_CASH' => 'RECEBIDO',
        ];

        $newStatus = (string) ($payment['status'] ?? '');

        if (isset($statusMap[$newStatus])) {
            $this->inscricaoService->atualizarStatus((int) ($inscricao['id'] ?? 0), $statusMap[$newStatus]);

            // Pagamento recebido/confirmado: garantir que a matricula foi criada.
            if (in_array($statusMap[$newStatus], ['RECEBIDO', 'CONFIRMADO'], true)) {
                return $this->confirmarPagamento($payment);
            }

            return [
                'message' => 'Status atualizado',
                'status' => $statusMap[$newStatus],
                'inscricaoId' => (int) ($inscricao['id'] ?? 0),
            ];
        }

        return [
            'message' => 'Status não mapeado',
            'status' => $newStatus,
            'inscricaoId' => (int) ($inscricao['id'] ?? 0),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $turmas
     * @return array<string, mixed>
     */
    private function selecionarTurma(array $turmas): array
    {
        foreach ($turmas as $turma) {
            if (intval($turma['ativo'] ?? 0) === 1) {
                return $turma;
            }
        }

        return $turmas[0];
    }
}
