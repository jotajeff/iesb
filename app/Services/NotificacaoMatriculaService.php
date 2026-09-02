<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NotificacaoMatriculaRepository;

final class NotificacaoMatriculaService
{
    public function __construct(
        private readonly NotificacaoMatriculaRepository $repository = new NotificacaoMatriculaRepository(),
        private readonly EmailService $emailService = new EmailService(),
        private readonly AlunoService $alunoService = new AlunoService(),
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        return $this->repository->listar();
    }

    /**
     * @return array<string, bool>
     */
    public function mapaStatus(): array
    {
        return $this->repository->mapaStatus();
    }

    public function enviar(int $idAluno, int $idCurso, string $nome, string $email, string $cpf, int $idMatricula, bool $forcar = false): bool
    {
        $email = trim($email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->repository->criar($idAluno, $idCurso, $email, false);
            return false;
        }

        if (!$forcar && $this->repository->jaEnviada($idAluno, $idCurso, $email)) {
            return true;
        }

        $senha = explode('@', $email)[0] . '#' . date('Y');
        $linkRedefinicao = '';

        try {
            if ($idAluno > 0) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $this->alunoService->salvarResetToken($idAluno, $token, $expires);

                $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = (string) ($_SERVER['HTTP_HOST'] ?? 'inteligenciaeducacionalsouzabrazil.com');
                $linkRedefinicao = "{$scheme}://{$host}/aluno/redefinir-senha?token={$token}";
            }

            $enviado = $this->emailService->enviarBoasVindasMatricula(
                $nome,
                $email,
                $cpf,
                $senha,
                (string) $idMatricula,
                $linkRedefinicao
            );
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao enviar e-mail: ' . $e->getMessage());
            $enviado = false;
        }

        $this->repository->criar($idAluno, $idCurso, $email, $enviado);
        if (!$enviado) {
            error_log('[NOTIFICACAO_MATRICULA] E-mail não enviado: ' . $this->emailService->getLastError());
        }

        return $enviado;
    }

    public function reenviarPorId(int $idNotificacao): array
    {
        $notificacao = $this->repository->buscarPorId($idNotificacao);
        if ($notificacao === null) {
            return ['sucesso' => false, 'mensagem' => 'Aviso de matrícula não encontrado.'];
        }

        $idAluno = (int) ($notificacao['id_aluno'] ?? 0);
        $idCurso = (int) ($notificacao['id_curso'] ?? 0);
        $idMatricula = (int) ($notificacao['id_matricula'] ?? 0);
        if ($idAluno <= 0 || $idCurso <= 0 || $idMatricula <= 0) {
            return ['sucesso' => false, 'mensagem' => 'Não foi possível localizar a matrícula vinculada.'];
        }

        $aluno = $this->alunoService->findAluno($idAluno);
        if ($aluno === null) {
            return ['sucesso' => false, 'mensagem' => 'Aluno não encontrado.'];
        }

        $email = trim((string) ($aluno['email'] ?? $notificacao['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'mensagem' => 'O aluno não possui um e-mail válido cadastrado.'];
        }

        $enviado = $this->enviar(
            $idAluno,
            $idCurso,
            (string) ($aluno['nome'] ?? $notificacao['aluno_nome'] ?? ''),
            $email,
            (string) ($aluno['cpf'] ?? $notificacao['aluno_cpf'] ?? ''),
            $idMatricula,
            true
        );

        return [
            'sucesso' => $enviado,
            'mensagem' => $enviado
                ? 'E-mail de matrícula reenviado com sucesso.'
                : 'Não foi possível reenviar o e-mail de matrícula.' . ($this->emailService->getLastError() !== '' ? ' Motivo: ' . $this->emailService->getLastError() : ''),
        ];
    }
}
