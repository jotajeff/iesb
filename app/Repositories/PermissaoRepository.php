<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class PermissaoRepository
{
    public function listarModulos(): array
    {
        return $this->fetchAll('SELECT id, nome, rota, icone, ordem, ativo FROM modulo ORDER BY ordem ASC, nome ASC');
    }

    public function buscarModulo(int $id): ?array
    {
        return $this->fetchOne('SELECT id, nome, rota, icone, ordem, ativo FROM modulo WHERE id = :id LIMIT 1', [':id' => $id]);
    }

    public function salvarModulo(array $dados): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if ((int) ($dados['id'] ?? 0) > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE modulo SET nome = :nome, rota = :rota, icone = :icone, ordem = :ordem, ativo = :ativo WHERE id = :id'
                );
                $stmt->bindValue(':id', (int) $dados['id'], PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO modulo (nome, rota, icone, ordem, ativo) VALUES (:nome, :rota, :icone, :ordem, :ativo)'
                );
            }

            $stmt->bindValue(':nome', (string) ($dados['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':rota', ($dados['rota'] ?? '') !== '' ? $dados['rota'] : null, ($dados['rota'] ?? '') !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':icone', ($dados['icone'] ?? '') !== '' ? $dados['icone'] : null, ($dados['icone'] ?? '') !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':ordem', (int) ($dados['ordem'] ?? 0), PDO::PARAM_INT);
            $stmt->bindValue(':ativo', (int) ($dados['ativo'] ?? 1) === 1 ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            return (int) ($dados['id'] ?? 0) > 0 ? (int) $dados['id'] : (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[PERMISSOES] Erro ao salvar módulo: ' . $e->getMessage());
            return 0;
        }
    }

    public function listarFuncoes(): array
    {
        return $this->fetchAll('SELECT id, nome, descricao, ativo FROM usuarios_funcao ORDER BY nome ASC');
    }

    public function buscarFuncao(int $id): ?array
    {
        return $this->fetchOne('SELECT id, nome, descricao, ativo FROM usuarios_funcao WHERE id = :id LIMIT 1', [':id' => $id]);
    }

    public function salvarFuncao(array $dados): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            if ((int) ($dados['id'] ?? 0) > 0) {
                $stmt = $pdo->prepare('UPDATE usuarios_funcao SET nome = :nome, descricao = :descricao, ativo = :ativo WHERE id = :id');
                $stmt->bindValue(':id', (int) $dados['id'], PDO::PARAM_INT);
            } else {
                $stmt = $pdo->prepare('INSERT INTO usuarios_funcao (nome, descricao, ativo) VALUES (:nome, :descricao, :ativo)');
            }

            $stmt->bindValue(':nome', (string) ($dados['nome'] ?? ''), PDO::PARAM_STR);
            $stmt->bindValue(':descricao', ($dados['descricao'] ?? '') !== '' ? $dados['descricao'] : null, ($dados['descricao'] ?? '') !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(':ativo', (int) ($dados['ativo'] ?? 1) === 1 ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();

            return (int) ($dados['id'] ?? 0) > 0 ? (int) $dados['id'] : (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[PERMISSOES] Erro ao salvar função: ' . $e->getMessage());
            return 0;
        }
    }

    public function permissoesDaFuncao(int $idFuncao): array
    {
        $rows = $this->fetchAll(
            'SELECT id, id_funcao, id_modulo, consultar, inserir, editar, excluir, ativo
             FROM usuarios_funcao_permissao WHERE id_funcao = :id_funcao',
            [':id_funcao' => $idFuncao]
        );

        $permissoes = [];
        foreach ($rows as $row) {
            $permissoes[(int) $row['id_modulo']] = $row;
        }
        return $permissoes;
    }

    public function salvarPermissoes(int $idFuncao, array $permissoes): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO || $idFuncao <= 0) {
            return false;
        }

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios_funcao_permissao
                    (id_funcao, id_modulo, consultar, inserir, editar, excluir, ativo)
                 VALUES (:id_funcao, :id_modulo, :consultar, :inserir, :editar, :excluir, 1)
                 ON DUPLICATE KEY UPDATE
                    consultar = VALUES(consultar), inserir = VALUES(inserir),
                    editar = VALUES(editar), excluir = VALUES(excluir), ativo = 1'
            );

            foreach ($permissoes as $idModulo => $acoes) {
                $idModulo = (int) $idModulo;
                if ($idModulo <= 0) {
                    continue;
                }
                $stmt->bindValue(':id_funcao', $idFuncao, PDO::PARAM_INT);
                $stmt->bindValue(':id_modulo', $idModulo, PDO::PARAM_INT);
                $stmt->bindValue(':consultar', !empty($acoes['consultar']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':inserir', !empty($acoes['inserir']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':editar', !empty($acoes['editar']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->bindValue(':excluir', !empty($acoes['excluir']) ? 1 : 0, PDO::PARAM_INT);
                $stmt->execute();
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[PERMISSOES] Erro ao salvar permissões: ' . $e->getMessage());
            return false;
        }
    }

    private function fetchAll(string $sql, array $params = []): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[PERMISSOES] Erro ao consultar: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $rows = $this->fetchAll($sql, $params);
        return $rows[0] ?? null;
    }
}
