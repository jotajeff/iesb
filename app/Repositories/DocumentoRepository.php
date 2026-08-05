<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class DocumentoRepository
{
    public function create(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO documento
                (id_grupo, id_registro, id_tipo, nome_original, nome_drive, folder_id, mime_type, tamanho, file_id, versao)
                VALUES (:id_grupo, :id_registro, :id_tipo, :nome_original, :nome_drive, :folder_id, :mime_type, :tamanho, :file_id, :versao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id_grupo', (int) ($data['id_grupo'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_registro', (int) ($data['id_registro'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':id_tipo', (int) ($data['id_tipo'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':nome_original', $data['nome_original'] ?? '');
        $stmt->bindValue(':nome_drive', $data['nome_drive'] ?? '');
        $stmt->bindValue(':folder_id', $data['folder_id'] ?? null);
        $stmt->bindValue(':mime_type', $data['mime_type'] ?? null);
        $stmt->bindValue(':tamanho', isset($data['tamanho']) ? (int) $data['tamanho'] : null, PDO::PARAM_INT);
        $stmt->bindValue(':file_id', $data['file_id'] ?? '');
        $stmt->bindValue(':versao', (int) ($data['versao'] ?? 1), PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function findByFileId(string $fileId): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, id_grupo, id_registro, id_tipo, nome_original, nome_drive, folder_id, mime_type, tamanho, file_id, versao, ativo
                FROM documento
                WHERE file_id = :file_id AND ativo = 1
                ORDER BY id DESC
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':file_id', $fileId);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, id_grupo, id_registro, id_tipo, nome_original, nome_drive, folder_id, mime_type, tamanho, file_id, versao, ativo
                FROM documento
                WHERE id = :id AND ativo = 1
                LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function listByRegistro(int $idGrupo, int $idRegistro, ?int $idTipo = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, id_grupo, id_registro, id_tipo, nome_original, nome_drive, folder_id, mime_type, tamanho, file_id, versao, ativo, created_at
                FROM documento
                WHERE id_grupo = :id_grupo AND id_registro = :id_registro AND ativo = 1';
        $params = [
            ':id_grupo' => (int) $idGrupo,
            ':id_registro' => (int) $idRegistro,
        ];

        if ($idTipo !== null && $idTipo > 0) {
            $sql .= ' AND id_tipo = :id_tipo';
            $params[':id_tipo'] = (int) $idTipo;
        }

        $sql .= ' ORDER BY id DESC';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function softDelete(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE documento SET ativo = 0 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function update(int $id, array $data): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $allowed = ['nome_original', 'nome_drive', 'folder_id', 'mime_type', 'tamanho', 'file_id', 'versao'];
        $sets = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowed, true)) {
                continue;
            }
            $sets[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        if ($sets === []) {
            return;
        }

        $params[':id'] = $id;
        $sql = 'UPDATE documento SET ' . implode(', ', $sets) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();
    }

    public function setFileId(int $id, string $fileId, string $folderId): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE documento SET file_id = :file_id, folder_id = :folder_id WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':file_id', $fileId);
        $stmt->bindValue(':folder_id', $folderId);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
