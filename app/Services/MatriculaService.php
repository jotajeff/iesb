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

        if (!empty($inscricao['id_matricula']) || ($inscricao['status'] ?? '') === 'CONFIRMADO') {
            return [
                'message' => 'Pagamento já processado anteriormente',
                'inscricaoId' => $idInscricao,
                'alunoId' => isset($inscricao['id_aluno']) ? (int) $inscricao['id_aluno'] : null,
                'matriculaId' => isset($inscricao['id_matricula']) ? (int) $inscricao['id_matricula'] : null,
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

            $turma = $this->selecionarTurma($turmas);
            $idTurma = (int) ($turma['id'] ?? 0);
            if ($idTurma <= 0) {
                throw new RuntimeException('Turma inválida');
            }

            if ($this->alunoService->matriculaJaExiste($idAluno, $idTurma)) {
                $matricula = $this->alunoService->findMatriculaByAlunoAndTurma($idAluno, $idTurma);
                $idMatricula = (int) ($matricula['id'] ?? 0);
            } else {
                $idMatricula = $this->alunoService->criarMatricula($idAluno, $idTurma);
                if ($idMatricula <= 0) {
                    throw new RuntimeException('Falha ao criar matrícula');
                }
            }

            $this->inscricaoService->atualizarStatus($idInscricao, 'CONFIRMADO', $idAluno, $idMatricula);

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
            if (($turma['ativa'] ?? 'N') === 'S') {
                return $turma;
            }
        }

        return $turmas[0];
    }
}
