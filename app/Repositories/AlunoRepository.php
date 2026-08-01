<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AlunoRepository
{
    public function list(int $limit = 200, ?int $ativo = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, nome, cpf, data_nascimento, telefone, email, ativo,
                     (SELECT COUNT(*) FROM matriculas WHERE id_aluno = alunos.id) AS total_matriculas
                     FROM alunos';

            if ($ativo !== null) {
                $sql .= ' WHERE ativo = ' . ($ativo === 1 ? '1' : '0');
            }

            $sql .= ' ORDER BY nome ASC LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em list: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, nome, cpf, data_nascimento, telefone, email, foto, ativo, created_at, updated_at
                     FROM alunos
                     WHERE id = :id
                     LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findById: ' . $e->getMessage());
            return null;
        }
    }

    public function findByEmail(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, nome, email, senha, ativo
                     FROM alunos
                     WHERE email = :email
                     LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findByEmail: ' . $e->getMessage());
            return null;
        }
    }

    public function findByCpf(string $cpf): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, nome, cpf, email, telefone, ativo FROM alunos WHERE cpf = :cpf LIMIT 1');
            $stmt->bindValue(':cpf', $cpf);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findByCpf: ' . $e->getMessage());
            return null;
        }
    }

    public function findByResetToken(string $token): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, nome, email, reset_token, reset_token_expires'
                . ' FROM alunos'
                . ' WHERE reset_token = :token AND reset_token_expires > NOW()'
                . ' LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em findByResetToken: ' . $e->getMessage());
            return null;
        }
    }

    public function save(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if (!empty($payload['id'])) {
                $set = [];
                $fields = ['nome', 'cpf', 'data_nascimento', 'telefone', 'email', 'ativo', 'senha', 'foto', 'reset_token', 'reset_token_expires'];
                foreach ($fields as $field) {
                    if (array_key_exists($field, $payload)) {
                        $set[] = "$field = :$field";
                    }
                }
                $sql = 'UPDATE alunos SET ' . implode(', ', $set) . ' WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $payload['id'], PDO::PARAM_INT);
            } else {
                $sql = 'INSERT INTO alunos (nome, cpf, data_nascimento, telefone, email, ativo, senha) VALUES (:nome, :cpf, :data_nascimento, :telefone, :email, :ativo, :senha)';
                $stmt = $pdo->prepare($sql);
            }

            if (array_key_exists('nome', $payload)) {
                $stmt->bindValue(':nome', trim($payload['nome']), PDO::PARAM_STR);
            }
            if (array_key_exists('cpf', $payload)) {
                $stmt->bindValue(':cpf', trim($payload['cpf']), PDO::PARAM_STR);
            }
            if (array_key_exists('data_nascimento', $payload)) {
                $stmt->bindValue(':data_nascimento', $payload['data_nascimento'] ?? null, PDO::PARAM_STR);
            }
            if (array_key_exists('telefone', $payload)) {
                $stmt->bindValue(':telefone', trim($payload['telefone']), PDO::PARAM_STR);
            }
            if (array_key_exists('email', $payload)) {
                $stmt->bindValue(':email', trim($payload['email']), PDO::PARAM_STR);
            }
            if (array_key_exists('ativo', $payload)) {
                $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 0) ? 1 : 0, PDO::PARAM_INT);
            }
            if (array_key_exists('senha', $payload)) {
                $stmt->bindValue(':senha', $payload['senha'], PDO::PARAM_STR);
            }
            if (array_key_exists('reset_token', $payload)) {
                $stmt->bindValue(':reset_token', $payload['reset_token'], $payload['reset_token'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            }
            if (array_key_exists('reset_token_expires', $payload)) {
                $stmt->bindValue(':reset_token_expires', $payload['reset_token_expires'], $payload['reset_token_expires'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            }
            $stmt->execute();

            if (empty($payload['id'])) {
                return (int) $pdo->lastInsertId();
            }
            return $payload['id'];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em save: ' . $e->getMessage());
            return 0;
        }
    }

    public function listMatriculasByAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id, m.id_aluno, m.id_turma, m.data_matricula, m.status, t.nome AS turma_nome
                     FROM matriculas m
                     LEFT JOIN turmas t ON m.id_turma = t.id
                     WHERE m.id_aluno = :id_aluno
                     ORDER BY m.data_matricula DESC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[MATRICULAS] Erro em listMatriculasByAluno: ' . $e->getMessage());
            return [];
        }
    }

    public function listCursosByAluno(int $idAluno): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT m.id AS matricula_id, m.status, m.data_matricula,
                     t.id AS turma_id, t.nome AS turma_nome,
                     c.id AS curso_id, c.nome AS curso_nome
                     FROM matriculas m
                     INNER JOIN turmas t ON m.id_turma = t.id
                     INNER JOIN cursos c ON t.id_curso = c.id
                     WHERE m.id_aluno = :id_aluno
                     ORDER BY c.nome ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[ALUNOS] Erro em listCursosByAluno: ' . $e->getMessage());
            return [];
        }
    }
}
