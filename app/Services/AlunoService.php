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

    public function alunos(int $limit = 200): array
    {
        return $this->repository->list($limit);
    }

    public function findAluno(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->repository->findById($id);
    }

    public function criarAluno(string $nome, string $cpf, string $dataNascimento, string $telefone, string $email, string $ativo = 'S'): int
    {
        $prefix = explode('@', $email)[0] ?? '';
        $senha = $prefix . '#' . date('Y');

        $payload = [
            'nome' => trim($nome),
            'cpf' => trim($cpf),
            'data_nascimento' => $dataNascimento,
            'telefone' => trim($telefone),
            'email' => trim($email),
            'ativo' => strtoupper(trim($ativo)) === 'S' ? 'S' : 'N',
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
        ];
        return $this->repository->save($payload);
    }

    public function atualizarAluno(int $id, string $nome, string $cpf, string $dataNascimento, string $telefone, string $email, string $ativo = 'S', ?string $senha = null): void
    {
        $payload = [
            'id' => $id,
            'nome' => trim($nome),
            'cpf' => trim($cpf),
            'data_nascimento' => $dataNascimento,
            'telefone' => trim($telefone),
            'email' => trim($email),
            'ativo' => strtoupper(trim($ativo)) === 'S' ? 'S' : 'N',
        ];
        if ($senha !== null && $senha !== '') {
            $payload['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        $this->repository->save($payload);
    }

    public function matriculasDoAluno(int $idAluno): array
    {
        return $this->repository->listMatriculasByAluno($idAluno);
    }

    public function cursosDoAluno(int $idAluno): array
    {
        return $this->repository->listCursosByAluno($idAluno);
    }

    public function criarMatricula(int $idAluno, int $idTurma, string $status = 'matriculado'): int
    {
        return $this->turmaRepository->saveMatricula([
            'id_aluno' => $idAluno,
            'id_turma' => $idTurma,
            'status' => $status,
        ]);
    }

    public function matriculaJaExiste(int $idAluno, int $idTurma): bool
    {
        return $this->turmaRepository->findMatriculaByAlunoAndTurma($idAluno, $idTurma) !== null;
    }

    public function atualizarSenha(int $id, string $senha): void
    {
        $this->repository->save([
            'id' => $id,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
    }
}
