<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class StorageDriveRepository
{
    public function findByRegistro(int $idGrupo, int $idRegistro, string $tipo = 'registro'): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, id_grupo, id_registro, folder_id, folder_name, folder_link, parent_folder_id, nivel, tipo, ativo
                FROM storage_drive
                WHERE id_grupo = :id_grupo AND id_registro = :id_registro AND tipo = :tipo AND ativo = 1
                ORDER BY id DESC
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_grupo', $idGrupo, PDO::PARAM_INT);
        $stmt->bindValue(':id_registro', $idRegistro, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $tipo);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findByGrupo(int $idGrupo): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, id_grupo, id_registro, folder_id, folder_name, folder_link, parent_folder_id, nivel, tipo, ativo
                FROM storage_drive
                WHERE id_grupo = :id_grupo AND tipo = :tipo AND ativo = 1
                ORDER BY id DESC
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_grupo', $idGrupo, PDO::PARAM_INT);
        $stmt->bindValue(':tipo', 'grupo');
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO storage_drive
                (id_grupo, id_registro, folder_id, folder_name, folder_link, parent_folder_id, nivel, tipo)
                VALUES (:id_grupo, :id_registro, :folder_id, :folder_name, :folder_link, :parent_folder_id, :nivel, :tipo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_grupo', (int) ($data['id_grupo'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_registro', (int) ($data['id_registro'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':folder_id', $data['folder_id'] ?? '');
        $stmt->bindValue(':folder_name', $data['folder_name'] ?? '');
        $stmt->bindValue(':folder_link', $data['folder_link'] ?? null);
        $stmt->bindValue(':parent_folder_id', $data['parent_folder_id'] ?? null);
        $stmt->bindValue(':nivel', (int) ($data['nivel'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':tipo', $data['tipo'] ?? 'registro');
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function updateFolderId(int $id, string $folderId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE storage_drive SET folder_id = :folder_id WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':folder_id', $folderId);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
