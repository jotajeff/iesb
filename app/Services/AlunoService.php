<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AlunoRepository;
use App\Repositories\TurmaRepository;

final class AlunoService
{
    public function __construct(
        private readonly AlunoRepository $repository = new AlunoRepository(),
        private readonly TurmaRepository $turmaRepository = new TurmaRepository(),
    ) {
    }

    public function alunos(int $limit = 200, ?int $ativo = null): array
    {
        return $this->repository->list($limit, $ativo);
    }

    public function findAluno(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function existeEmail(string $email): bool
    {
        return $this->repository->findByEmail($email) !== null;
    }

    public function criarAluno(string $nome, string $cpf, string $dataNascimento, string $telefone, string $email, int $ativo = 1): int
    {
        $prefix = explode('@', $email)[0] ?? '';
        $senha = $prefix . '#' . date('Y');

        $payload = [
            'nome' => trim($nome),
            'cpf' => trim($cpf),
            'data_nascimento' => $dataNascimento,
            'telefone' => trim($telefone),
            'email' => trim($email),
            'ativo' => $ativo ? 1 : 0,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
        ];
        return $this->repository->save($payload);
    }

    public function atualizarAluno(int $id, string $nome, string $cpf, string $dataNascimento, string $telefone, string $email, int $ativo = 1, ?string $senha = null): void
    {
        $payload = [
            'id' => $id,
            'nome' => trim($nome),
            'cpf' => trim($cpf),
            'data_nascimento' => $dataNascimento,
            'telefone' => trim($telefone),
            'email' => trim($email),
            'ativo' => $ativo ? 1 : 0,
        ];
        if ($senha !== null && $senha !== '') {
            $payload['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        $this->repository->save($payload);
    }

    public function matriculaDoAluno(int $idAluno): array
    {
        return $this->repository->listMatriculaByAluno($idAluno);
    }
    public function cursosDoAluno(int $idAluno): array
    {
        return $this->repository->listCursosByAluno($idAluno);
    }

    public function criarMatricula(int $idAluno, int $idTurma, string $status = 'matriculado', int $idPagamento = 0): int
    {
        return $this->turmaRepository->saveMatricula([
            'id_aluno' => $idAluno,
            'id_turma' => $idTurma,
            'status' => $status,
            'id_pagamento' => $idPagamento,
        ]);
    }

    public function matriculaJaExiste(int $idAluno, int $idTurma): bool
    {
        return $this->turmaRepository->findMatriculaByAlunoAndTurma($idAluno, $idTurma) !== null;
    }

    public function findMatriculaByAlunoAndTurma(int $idAluno, int $idTurma): ?array
    {
        return $this->turmaRepository->findMatriculaByAlunoAndTurma($idAluno, $idTurma);
    }

    public function findMatriculaById(int $idMatricula): ?array
    {
        if ($idMatricula <= 0) {
            return null;
        }
        return $this->turmaRepository->findMatriculaById($idMatricula);
    }

    public function atualizarMatriculaTurma(int $idMatricula, int $idNovaTurma): bool
    {
        return $this->turmaRepository->updateMatriculaTurma($idMatricula, $idNovaTurma);
    }

    public function registrarTroca(int $idOrigem, int $idDestino, int $idAluno, string $motivo): int
    {
        return $this->turmaRepository->insertTroca($idOrigem, $idDestino, $idAluno, $motivo);
    }

    public function atualizarFotoAluno(int $id, string $foto): void
    {
        $this->repository->save([
            'id' => $id,
            'foto' => $foto,
        ]);
    }

    public function atualizarSenha(int $id, string $senha): void
    {
        $this->repository->save([
            'id' => $id,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->repository->findByEmail($email);
    }

    public function findByCpf(string $cpf): ?array
    {
        return $this->repository->findByCpf($cpf);
    }

    public function salvarResetToken(int $id, string $token, string $expires): void
    {
        $this->repository->save([
            'id' => $id,
            'reset_token' => $token,
            'reset_token_expires' => $expires,
        ]);
    }

    public function buscarPorResetToken(string $token): ?array
    {
        return $this->repository->findByResetToken($token);
    }

    public function limparResetToken(int $id): void
    {
        $this->repository->save([
            'id' => $id,
            'reset_token' => null,
            'reset_token_expires' => null,
        ]);
    }
}
