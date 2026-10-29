<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class FuncaoUsuarioRepository
{
    public function listarUsuariosComFuncoes(): array
    {
        $rows = $this->fetchAll(
            'SELECT u.id, u.nome, u.email, u.ativo, u.created_at,'
            . ' f.id AS funcao_id, f.nome AS funcao_nome'
            . ' FROM usuarios u'
            . ' LEFT JOIN usuario_liga_funcao ulf ON ulf.id_usuario = u.id AND ulf.ativo = 1'
            . ' LEFT JOIN usuarios_funcao f ON f.id = ulf.id_funcao AND f.ativo = 1'
            . ' ORDER BY u.nome ASC, f.nome ASC'
        );

        $usuarios = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if (!isset($usuarios[$id])) {
                $usuarios[$id] = [
                    'id' => $id,
                    'nome' => (string) ($row['nome'] ?? ''),
                    'email' => (string) ($row['email'] ?? ''),
                    'ativo' => (int) ($row['ativo'] ?? 0),
                    'created_at' => $row['created_at'] ?? null,
                    'funcoes' => [],
                ];
            }

            $idFuncao = (int) ($row['funcao_id'] ?? 0);
            if ($idFuncao > 0) {
                $usuarios[$id]['funcoes'][] = [
                    'id' => $idFuncao,
                    'nome' => (string) ($row['funcao_nome'] ?? ''),
                ];
            }
        }

        return array_values($usuarios);
    }

    public function buscarUsuario(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, nome, email, tipo, ativo, created_at FROM usuarios WHERE id = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public function listarFuncoes(): array
    {
        return $this->fetchAll(
            'SELECT id, nome, descricao FROM usuarios_funcao WHERE ativo = 1 ORDER BY nome ASC'
        );
    }

    public function funcoesDoUsuario(int $idUsuario): array
    {
        $rows = $this->fetchAll(
            'SELECT f.id FROM usuario_liga_funcao uf'
            . ' INNER JOIN usuarios_funcao f ON f.id = uf.id_funcao'
            . ' WHERE uf.id_usuario = :id_usuario AND uf.ativo = 1 AND f.ativo = 1',
            [':id_usuario' => $idUsuario]
        );

        return array_map(static fn(array $row): int => (int) ($row['id'] ?? 0), $rows);
    }

    public function salvarFuncoesDoUsuario(int $idUsuario, array $idsFuncoes): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $idsFuncoes = array_values(array_unique(array_filter(
            array_map('intval', $idsFuncoes),
            static fn(int $id): bool => $id > 0
        )));

        try {
            $pdo->beginTransaction();

            $stmtDesativar = $pdo->prepare(
                'UPDATE usuario_liga_funcao SET ativo = 0 WHERE id_usuario = :id_usuario AND ativo = 1'
            );
            $stmtDesativar->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
            $stmtDesativar->execute();

            if (!empty($idsFuncoes)) {
                $stmtVincular = $pdo->prepare(
                    'INSERT INTO usuario_liga_funcao (id_usuario, id_funcao, ativo)'
                    . ' VALUES (:id_usuario, :id_funcao, 1)'
                    . ' ON DUPLICATE KEY UPDATE ativo = 1'
                );

                foreach ($idsFuncoes as $idFuncao) {
                    $stmtVincular->bindValue(':id_usuario', $idUsuario, PDO::PARAM_INT);
                    $stmtVincular->bindValue(':id_funcao', $idFuncao, PDO::PARAM_INT);
                    $stmtVincular->execute();
                }
            }

            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('[FUNCAO USUARIO] Erro ao salvar funções: ' . $e->getMessage());
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
            foreach ($params as $chave => $valor) {
                $stmt->bindValue($chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[FUNCAO USUARIO] Erro: ' . $e->getMessage());
            return [];
        }
    }

    private function fetchOne(string $sql, array $params = []): ?array
    {
        $rows = $this->fetchAll($sql, $params);
        return $rows[0] ?? null;
    }
}
