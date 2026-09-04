<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class BannerAlunoRepository
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function list(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->query(
                'SELECT b.id, b.banner, b.texto, b.link, b.id_curso, b.ativo, b.created_at,'
                . ' c.nome AS curso_nome'
                . ' FROM banner_aluno b'
                . ' LEFT JOIN cursos c ON c.id = b.id_curso'
                . ' ORDER BY b.id DESC'
            );

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[BANNER_ALUNO] Erro em list: ' . $e->getMessage());
            return [];
        }
    }

    public function find(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $id <= 0) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM banner_aluno WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            error_log('[BANNER_ALUNO] Erro em find: ' . $e->getMessage());
            return null;
        }
    }

    public function save(int $id, string $banner, ?string $texto, string $link, ?int $idCurso, int $ativo): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if ($id > 0) {
                $sql = 'UPDATE banner_aluno SET texto = :texto, link = :link, id_curso = :id_curso, ativo = :ativo';
                if ($banner !== '') {
                    $sql .= ', banner = :banner';
                }
                $sql .= ' WHERE id = :id';

                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
                $stmt->bindValue(':link', $link, PDO::PARAM_STR);
                $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
                $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
                if ($banner !== '') {
                    $stmt->bindValue(':banner', $banner, PDO::PARAM_STR);
                }
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO banner_aluno (banner, texto, link, id_curso, ativo, created_at)'
                . ' VALUES (:banner, :texto, :link, :id_curso, :ativo, NOW())'
            );
            $stmt->bindValue(':banner', $banner, PDO::PARAM_STR);
            $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
            $stmt->bindValue(':link', $link, PDO::PARAM_STR);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[BANNER_ALUNO] Erro em save: ' . $e->getMessage());
            return 0;
        }
    }
}
