<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class SessaoRepository
{
    public function list(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $stmt = $pdo->query('SELECT id, slug, badge, titulo, apresenta, banner, midia, criado_em FROM sessao ORDER BY criado_em DESC');
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[SESSAO] Erro ao listar: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $stmt = $pdo->prepare('SELECT * FROM sessao WHERE id = :id LIMIT 1');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[SESSAO] Erro ao buscar: ' . $e->getMessage());
            return null;
        }
    }

    public function save(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $slug = trim((string) ($data['slug'] ?? ''));
            $badge = trim((string) ($data['badge'] ?? ''));
            $apresenta = trim((string) ($data['apresenta'] ?? ''));
            $banner = trim((string) ($data['banner'] ?? ''));
            $titulo = trim((string) ($data['titulo'] ?? ''));
            $texto = (string) ($data['texto'] ?? '');
            $midia = $data['midia'] !== null && $data['midia'] !== '' ? (int) $data['midia'] : null;

            if ($id > 0) {
                $sql = 'UPDATE sessao SET slug = :slug, badge = :badge, apresenta = :apresenta,
                        banner = :banner, titulo = :titulo, texto = :texto, midia = :midia
                        WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':slug', $slug);
                $stmt->bindValue(':badge', $badge);
                $stmt->bindValue(':apresenta', $apresenta);
                $stmt->bindValue(':banner', $banner);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':texto', $texto);
                $stmt->bindValue(':midia', $midia, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO sessao (slug, badge, apresenta, banner, titulo, texto, midia)
                    VALUES (:slug, :badge, :apresenta, :banner, :titulo, :texto, :midia)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            $stmt->bindValue(':badge', $badge);
            $stmt->bindValue(':apresenta', $apresenta);
            $stmt->bindValue(':banner', $banner);
            $stmt->bindValue(':titulo', $titulo);
            $stmt->bindValue(':texto', $texto);
            $stmt->bindValue(':midia', $midia, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[SESSAO] Erro ao salvar: ' . $e->getMessage());
            return 0;
        }
    }

    public function delete(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $stmt = $pdo->prepare('DELETE FROM sessao WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[SESSAO] Erro ao deletar: ' . $e->getMessage());
        }
    }
}
