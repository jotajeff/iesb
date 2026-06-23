<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class ConfigRepository
{
    public function listModalidades(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome, ativo FROM modalidade ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar modalidades: ' . $e->getMessage());
            return [];
        }
    }

    public function findModalidadeById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, ativo FROM modalidade WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveModalidade(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $id = (int) ($payload['id'] ?? 0);
        $nome = trim((string) ($payload['nome'] ?? ''));
        $ativo = (int) ($payload['ativo'] ?? 1);

        if ($id > 0) {
            $sql = 'UPDATE modalidade SET nome = :nome, ativo = :ativo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return $id;
        }

        $sql = 'INSERT INTO modalidade (nome, ativo) VALUES (:nome, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listSegmentos(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome, ativo FROM segmento ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar segmentos: ' . $e->getMessage());
            return [];
        }
    }

    public function findSegmentoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, ativo FROM segmento WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveSegmento(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($payload['id'] ?? 0);
            $nome = trim((string) ($payload['nome'] ?? ''));
            $ativo = strtoupper(trim((string) ($payload['ativo'] ?? 'S'))) === 'N' ? 'N' : 'S';

            if ($id > 0) {
                $sql = 'UPDATE segmento SET nome = :nome, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':nome', $nome);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO segmento (nome, ativo) VALUES (:nome, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao salvar segmento: ' . $e->getMessage());
            return 0;
        }
    }

    public function listNiveis(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro ao carregar niveis: ' . $e->getMessage());
            return [];
        }
    }

    public function findNivelById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function findNivelBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $sql = 'SELECT id, slug, nome, ativo, apresentacao FROM nivel WHERE slug = :slug LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveNivel(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $id = (int) ($payload['id'] ?? 0);
        $nome = trim((string) ($payload['nome'] ?? ''));
        $ativo = (int) ($payload['ativo'] ?? 1);
        $apresentacao = (string) ($payload['apresentacao'] ?? '');

        if ($id > 0) {
            $sql = 'UPDATE nivel SET nome = :nome, ativo = :ativo, apresentacao = :apresentacao WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->bindValue(':apresentacao', $apresentacao);
            $stmt->execute();
            return $id;
        }

        $sql = 'INSERT INTO nivel (nome, ativo, apresentacao) VALUES (:nome, :ativo, :apresentacao)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->bindValue(':apresentacao', $apresentacao);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listInstituicoes(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, razao_social, nome_fantasia, dominio, documento, inscricao_estadual, telefone, email, responsavel_nome, tipo_cliente, status, criado_em, atualizado_em FROM instituicao ORDER BY razao_social ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CONFIG] Erro ao carregar instituicoes: ' . $e->getMessage());
            return [];
        }
    }

    public function findInstituicaoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, razao_social, nome_fantasia, dominio, documento, inscricao_estadual, telefone, email, senha, responsavel_nome, tipo_cliente, status, criado_em, atualizado_em FROM instituicao WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveInstituicao(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($payload['id'] ?? 0);
            $razaoSocial = trim((string) ($payload['razao_social'] ?? ''));
            $nomeFantasia = trim((string) ($payload['nome_fantasia'] ?? ''));
            $dominio = trim((string) ($payload['dominio'] ?? ''));
            $documento = trim((string) ($payload['documento'] ?? ''));
            $inscricaoEstadual = trim((string) ($payload['inscricao_estadual'] ?? ''));
            $telefone = trim((string) ($payload['telefone'] ?? ''));
            $email = trim((string) ($payload['email'] ?? ''));
            $responsavelNome = trim((string) ($payload['responsavel_nome'] ?? ''));
            $tipoCliente = trim((string) ($payload['tipo_cliente'] ?? 'PJ'));
            $status = trim((string) ($payload['status'] ?? 'Ativo'));
            $senha = (string) ($payload['senha'] ?? '');

            if ($razaoSocial === '' || $documento === '' || $email === '') {
                return 0;
            }

            if ($id > 0) {
                $sql = 'UPDATE instituicao SET razao_social = :razao_social, nome_fantasia = :nome_fantasia, dominio = :dominio, documento = :documento, inscricao_estadual = :inscricao_estadual, telefone = :telefone, email = :email, senha = :senha, responsavel_nome = :responsavel_nome, tipo_cliente = :tipo_cliente, status = :status WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':razao_social', $razaoSocial);
                $stmt->bindValue(':nome_fantasia', $nomeFantasia);
                $stmt->bindValue(':dominio', $dominio);
                $stmt->bindValue(':documento', $documento);
                $stmt->bindValue(':inscricao_estadual', $inscricaoEstadual);
                $stmt->bindValue(':telefone', $telefone);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':senha', $senha);
                $stmt->bindValue(':responsavel_nome', $responsavelNome);
                $stmt->bindValue(':tipo_cliente', $tipoCliente);
                $stmt->bindValue(':status', $status);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO instituicao (razao_social, nome_fantasia, dominio, documento, inscricao_estadual, telefone, email, senha, responsavel_nome, tipo_cliente, status) VALUES (:razao_social, :nome_fantasia, :dominio, :documento, :inscricao_estadual, :telefone, :email, :senha, :responsavel_nome, :tipo_cliente, :status)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':razao_social', $razaoSocial);
            $stmt->bindValue(':nome_fantasia', $nomeFantasia);
            $stmt->bindValue(':dominio', $dominio);
            $stmt->bindValue(':documento', $documento);
            $stmt->bindValue(':inscricao_estadual', $inscricaoEstadual);
            $stmt->bindValue(':telefone', $telefone);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':senha', $senha);
            $stmt->bindValue(':responsavel_nome', $responsavelNome);
            $stmt->bindValue(':tipo_cliente', $tipoCliente);
            $stmt->bindValue(':status', $status);
            $stmt->execute();

            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CONFIG] Erro ao salvar instituicao: ' . $e->getMessage());
            return 0;
        }
    }

    public function listCategorias(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome, slug, ativo FROM categoria_noticia ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CONFIG] Erro ao carregar categorias: ' . $e->getMessage());
            return [];
        }
    }

    public function findCategoriaById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT id, nome, slug, ativo FROM categoria_noticia WHERE id = :id LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function saveCategoria(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $id = (int) ($payload['id'] ?? 0);
        $nome = trim((string) ($payload['nome'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));
        $ativo = (int) ($payload['ativo'] ?? 1);

        if ($id > 0) {
            $sql = 'UPDATE categoria_noticia SET nome = :nome, slug = :slug, ativo = :ativo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nome', $nome);
            $stmt->bindValue(':slug', $slug);
            $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
            $stmt->execute();
            return $id;
        }

        $sql = 'INSERT INTO categoria_noticia (nome, slug, ativo) VALUES (:nome, :slug, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':slug', $slug);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listSegmentosByNivel(int $nivelId): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        if ($nivelId <= 0) {
            return [];
        }

        $sql = 'SELECT DISTINCT s.id, s.nome, s.ativo
                FROM cursos_iesb c
                INNER JOIN segmento s ON s.id = c.segmento
                WHERE c.ativo = "S" AND s.ativo = "S" AND c.nivel = :nivel_id
                ORDER BY s.nome ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nivel_id', $nivelId, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
