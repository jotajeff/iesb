<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class NotificacaoMatriculaRepository
{
    public function criar(int $idAluno, int $idCurso, string $email, bool $enviado): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0 || $idCurso <= 0) {
            return 0;
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO notificacao_matricula
                (id_aluno, id_curso, email, status, created_at)
                VALUES (:id_aluno, :id_curso, :email, :status, CURRENT_TIMESTAMP)');
            $stmt->bindValue(':id_aluno', $idAluno, PDO::PARAM_INT);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':status', $enviado ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao registrar envio: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listar(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT n.id, n.id_aluno, n.id_curso, n.email, n.status, n.created_at,'
                . ' a.nome AS aluno_nome, c.nome AS curso_nome'
                . ' FROM notificacao_matricula n'
                . ' LEFT JOIN alunos a ON a.id = n.id_aluno'
                . ' LEFT JOIN cursos c ON c.id = n.id_curso'
                . ' ORDER BY n.id DESC'
            );

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao listar envios: ' . $e->getMessage());
            return [];
        }
    }

    public function buscarPorId(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT n.*, a.nome AS aluno_nome, a.cpf AS aluno_cpf, a.email AS aluno_email,
                        a.telefone AS aluno_telefone, m.id AS id_matricula, m.numero AS numero_matricula
                 FROM notificacao_matricula n
                 INNER JOIN alunos a ON a.id = n.id_aluno
                 LEFT JOIN matricula m ON m.id_aluno = n.id_aluno AND m.id_curso = n.id_curso
                 WHERE n.id = :id
                 ORDER BY m.id DESC
                 LIMIT 1'
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao buscar notificação: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return array<string, bool>
     */
    public function mapaStatus(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT id_aluno, id_curso, status FROM notificacao_matricula ORDER BY id DESC'
            );
            $mapa = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $key = (int) ($row['id_aluno'] ?? 0) . ':' . (int) ($row['id_curso'] ?? 0);
                if (!isset($mapa[$key])) {
                    $mapa[$key] = (int) ($row['status'] ?? 0) === 1;
                }
            }
            return $mapa;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao montar mapa de status: ' . $e->getMessage());
            return [];
        }
    }

    public function jaEnviada(int $idAluno, int $idCurso, string $email): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idAluno <= 0 || $idCurso <= 0 || $email === '') {
            return false;
        }

        try {
            $stmt = $pdo->prepare('SELECT 1 FROM notificacao_matricula
                WHERE id_aluno = :id_aluno AND id_curso = :id_curso
                  AND email = :email AND status = 1
                ORDER BY id DESC LIMIT 1');
            $stmt->execute([
                'id_aluno' => $idAluno,
                'id_curso' => $idCurso,
                'email' => $email,
            ]);

            return $stmt->fetchColumn() !== false;
        } catch (\Throwable $e) {
            error_log('[NOTIFICACAO_MATRICULA] Erro ao verificar envio: ' . $e->getMessage());
            return false;
        }
    }
}
