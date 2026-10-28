<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class UserRepository extends JsonRepository
{
    public function __construct()
    {
        parent::__construct(dirname(__DIR__, 2) . '/storage/users.json');
    }

    public function findByEmail(string $email): ?array
    {
        $fromDatabase = $this->findByEmailInDatabase($email);
        if ($fromDatabase !== null) {
            return $fromDatabase;
        }

        foreach ($this->allData() as $user) {
            if (mb_strtolower((string) $user['email']) === mb_strtolower($email)) {
                return $user;
            }
        }

        return null;
    }

    public function findById(int $id): ?array
    {
        $fromDatabase = $this->findByIdInDatabase($id);
        if ($fromDatabase !== null) {
            return $fromDatabase;
        }

        foreach ($this->allData() as $user) {
            if ((int) $user['id'] === $id) {
                return $user;
            }
        }

        return null;
    }

    private function findByEmailInDatabase(string $email): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            error_log('[DEBUG LOGIN DB] Database::connection() retornou null');
            return null;
        }

        $sql = 'SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE email = :email LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        error_log(sprintf('[DEBUG LOGIN DB] SQL encontrou row: %s', $row !== false ? 'sim' : 'nao'));
        if (is_array($row)) {
            error_log(sprintf('[DEBUG LOGIN DB] row dados: id=%s nome=%s email=%s tipo=%s ativo=%s senha(resumida)=%s',
                $row['id'] ?? '?', $row['nome'] ?? '?', $row['email'] ?? '?', $row['tipo'] ?? '?', $row['ativo'] ?? '?', substr((string) ($row['senha'] ?? ''), 0, 20) . '...'));
        }

        if (!is_array($row) || (int) ($row['ativo'] ?? 0) !== 1) {
            error_log(sprintf('[DEBUG LOGIN DB] filtro ativo barrou: is_array=%s, ativo=%s', is_array($row) ? 'sim' : 'nao', (string) ($row['ativo'] ?? 'null')));
            return null;
        }

        $user = [
            'id' => (int) $row['id'],
            'name' => (string) $row['nome'],
            'email' => (string) $row['email'],
            'password' => (string) $row['senha'],
            // tipo é mantido apenas como compatibilidade com dados legados.
            'legacy_role' => (string) $row['tipo'],
        ];

        return $this->withAccessProfile($user);
    }

    private function findByIdInDatabase(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, email, senha, tipo, ativo FROM usuarios WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if (!is_array($row) || (int) ($row['ativo'] ?? 0) !== 1) {
            return null;
        }

        $user = [
            'id' => (int) $row['id'],
            'name' => (string) $row['nome'],
            'email' => (string) $row['email'],
            'password' => (string) $row['senha'],
            'legacy_role' => (string) $row['tipo'],
        ];

        return $this->withAccessProfile($user);
    }

    /**
     * Resolve o acesso exclusivamente pelo vínculo usuário -> função -> módulo.
     * O campo usuarios.tipo não participa da autorização nova.
     */
    private function withAccessProfile(array $user): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            $legacyRole = (string) ($user['legacy_role'] ?? '');
            $user['roles'] = $legacyRole !== '' ? [$legacyRole] : [];
            $user['role'] = $legacyRole;
            $user['permissions'] = [];
            return $user;
        }

        try {
            $stmt = $pdo->prepare(
                'SELECT uf.id, uf.nome
                 FROM usuario_liga_funcao ulf
                 INNER JOIN usuarios_funcao uf ON uf.id = ulf.id_funcao
                 WHERE ulf.id_usuario = :id AND ulf.ativo = 1 AND uf.ativo = 1
                 ORDER BY uf.id ASC'
            );
            $stmt->execute(['id' => (int) $user['id']]);
            $functions = $stmt->fetchAll() ?: [];

            $roles = [];
            $functionIds = [];
            foreach ($functions as $function) {
                $name = $this->normalizeRole((string) ($function['nome'] ?? ''));
                if ($name !== '') {
                    $roles[] = $name;
                }
                $functionIds[] = (int) ($function['id'] ?? 0);
            }

            $permissions = [];
            if ($functionIds !== []) {
                $placeholders = implode(',', array_fill(0, count($functionIds), '?'));
                $permissionStmt = $pdo->prepare(
                    "SELECT m.rota, MAX(fp.consultar) AS consultar, MAX(fp.inserir) AS inserir,
                            MAX(fp.editar) AS editar, MAX(fp.excluir) AS excluir
                     FROM usuarios_funcao_permissao fp
                     INNER JOIN modulo m ON m.id = fp.id_modulo AND m.ativo = 1
                     WHERE fp.id_funcao IN ($placeholders) AND fp.ativo = 1 AND m.rota IS NOT NULL
                     GROUP BY m.id, m.rota"
                );
                $permissionStmt->execute($functionIds);
                foreach ($permissionStmt->fetchAll() ?: [] as $permission) {
                    $route = $this->normalizeRoute((string) ($permission['rota'] ?? ''));
                    if ($route === '') {
                        continue;
                    }
                    $permissions[$route] = [
                        'consultar' => (int) ($permission['consultar'] ?? 0),
                        'inserir' => (int) ($permission['inserir'] ?? 0),
                        'editar' => (int) ($permission['editar'] ?? 0),
                        'excluir' => (int) ($permission['excluir'] ?? 0),
                    ];
                }
            }

            $user['functions'] = $functions;
            $user['roles'] = array_values(array_unique($roles));
            $user['role'] = $user['roles'][0] ?? '';
            $user['permissions'] = $permissions;
            return $user;
        } catch (\Throwable $e) {
            error_log('[AUTH] Erro ao carregar funções/permissões: ' . $e->getMessage());
            $user['functions'] = [];
            $user['roles'] = [];
            $user['role'] = '';
            $user['permissions'] = [];
            return $user;
        }
    }

    private function normalizeRole(string $role): string
    {
        $role = mb_strtolower(trim($role));
        $role = strtr($role, ['á' => 'a', 'ã' => 'a', 'â' => 'a', 'ç' => 'c', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o', 'ú' => 'u']);
        return match ($role) {
            'administrador' => 'admin',
            'coordenacao' => 'coordenador',
            default => $role,
        };
    }

    private function normalizeRoute(string $route): string
    {
        $route = parse_url(trim($route), PHP_URL_PATH) ?: trim($route);
        return '/' . trim($route, '/');
    }
}
