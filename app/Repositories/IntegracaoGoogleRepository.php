<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class IntegracaoGoogleRepository
{
    public function findActive(): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, client_id, client_secret, refresh_token, root_folder_id, root_folder_nome,
                       email_workspace, conectado, ativo, provedor
                FROM integracao_google
                WHERE ativo = 1
                ORDER BY id ASC
                LIMIT 1';
        $stmt = $pdo->query($sql);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, client_id, client_secret, refresh_token, root_folder_id, root_folder_nome,
                       email_workspace, conectado, ativo, provedor
                FROM integracao_google
                WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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

        $sql = 'INSERT INTO integracao_google
                (nome, client_id, client_secret, refresh_token, root_folder_id, root_folder_nome, email_workspace, conectado, provedor)
                VALUES (:nome, :client_id, :client_secret, :refresh_token, :root_folder_id, :root_folder_nome, :email_workspace, :conectado, :provedor)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $data['nome'] ?? 'Google Drive');
        $stmt->bindValue(':client_id', $data['client_id'] ?? '');
        $stmt->bindValue(':client_secret', $data['client_secret'] ?? '');
        $stmt->bindValue(':refresh_token', $data['refresh_token'] ?? null);
        $stmt->bindValue(':root_folder_id', $data['root_folder_id'] ?? null);
        $stmt->bindValue(':root_folder_nome', $data['root_folder_nome'] ?? null);
        $stmt->bindValue(':email_workspace', $data['email_workspace'] ?? null);
        $stmt->bindValue(':conectado', (int) ($data['conectado'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':provedor', $data['provedor'] ?? 'google_drive');
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function saveTokens(int $id, string $refreshToken, string $email): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE integracao_google
                SET refresh_token = :refresh_token, email_workspace = :email_workspace, conectado = 1
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':refresh_token', $refreshToken);
        $stmt->bindValue(':email_workspace', $email);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function saveRootFolder(int $id, string $rootFolderId, string $rootFolderNome): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE integracao_google
                SET root_folder_id = :root_folder_id, root_folder_nome = :root_folder_nome
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':root_folder_id', $rootFolderId);
        $stmt->bindValue(':root_folder_nome', $rootFolderNome);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function setConectado(int $id, bool $conectado): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE integracao_google SET conectado = :conectado WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':conectado', $conectado ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function setDesconectado(int $id): void
    {
        $this->setConectado($id, false);
    }

    public function clearTokens(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE integracao_google SET refresh_token = NULL, email_workspace = NULL, conectado = 0 WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
