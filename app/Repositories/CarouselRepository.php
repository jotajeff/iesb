<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CarouselRepository
{
    public function listAllItemsAtivos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT ci.id, ci.id_carousel, ci.titulo, ci.subtitulo, ci.imagem, ci.link,
                           ci.target, ci.texto_botao, ci.ordem, ci.ativo, ci.criado_em
                    FROM carousel_item ci
                    WHERE ci.ativo = :ativo
                    ORDER BY ci.ordem ASC, ci.id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':ativo', 'S');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao listar itens ativos: ' . $e->getMessage());
            return [];
        }
    }

    public function listAllItems(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT ci.id, ci.id_carousel, ci.titulo, ci.subtitulo, ci.imagem, ci.link,
                           ci.target, ci.texto_botao, ci.ordem, ci.ativo, ci.criado_em
                    FROM carousel_item ci
                    ORDER BY ci.ordem ASC, ci.id DESC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao listar itens: ' . $e->getMessage());
            return [];
        }
    }

    public function findItemById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT ci.id, ci.id_carousel, ci.titulo, ci.subtitulo, ci.imagem, ci.link,
                           ci.target, ci.texto_botao, ci.ordem, ci.ativo, ci.criado_em
                    FROM carousel_item ci
                    WHERE ci.id = :id
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao buscar item: ' . $e->getMessage());
            return null;
        }
    }

    public function list(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, titulo, slug, ativo, criado_em, criado_por
                    FROM carousel
                    ORDER BY criado_em DESC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao listar carousels: ' . $e->getMessage());
            return [];
        }
    }

    public function listAtivos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, titulo, descricao, slug, link, ativo, criado_em, criado_por
                    FROM carousel
                    WHERE ativo = :ativo
                    ORDER BY criado_em DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':ativo', 'S');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao listar carousels ativos: ' . $e->getMessage());
            return [];
        }
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, titulo, descricao, slug, link, ativo, criado_em, criado_por
                    FROM carousel WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao buscar carousel: ' . $e->getMessage());
            return null;
        }
    }

    public function findItemsByCarouselId(int $idCarousel): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id, id_carousel, titulo, subtitulo, imagem, link, target,
                           texto_botao, ordem, data_inicio, data_fim, ativo, criado_em
                    FROM carousel_item
                    WHERE id_carousel = :id_carousel
                    ORDER BY ordem ASC, id ASC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_carousel', $idCarousel, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao buscar itens: ' . $e->getMessage());
            return [];
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
            $titulo = trim((string) ($data['titulo'] ?? ''));
            $descricao = trim((string) ($data['descricao'] ?? ''));
            $slug = trim((string) ($data['slug'] ?? ''));
            $link = trim((string) ($data['link'] ?? ''));
            $ativo = strtoupper(trim((string) ($data['ativo'] ?? 'S'))) === 'S' ? 'S' : 'N';

            if ($id > 0) {
                $sql = 'UPDATE carousel SET titulo = :titulo, descricao = :descricao, slug = :slug, link = :link, ativo = :ativo WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':descricao', $descricao);
                $stmt->bindValue(':slug', $slug);
                $stmt->bindValue(':link', $link);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO carousel (titulo, descricao, slug, link, ativo, criado_por)
                    VALUES (:titulo, :descricao, :slug, :link, :ativo, :criado_por)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':titulo', $titulo);
            $stmt->bindValue(':descricao', $descricao);
            $stmt->bindValue(':slug', $slug);
            $stmt->bindValue(':link', $link);
            $stmt->bindValue(':ativo', $ativo);
            $stmt->bindValue(':criado_por', (int) ($data['criado_por'] ?? 1), PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao salvar carousel: ' . $e->getMessage());
            return 0;
        }
    }

    public function saveItem(array $data): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $id = (int) ($data['id'] ?? 0);
            $idCarousel = (int) ($data['id_carousel'] ?? 0);
            $imagem = trim((string) ($data['imagem'] ?? ''));
            $titulo = trim((string) ($data['titulo'] ?? ''));
            $subtitulo = trim((string) ($data['subtitulo'] ?? ''));
            $link = trim((string) ($data['link'] ?? ''));
            $target = trim((string) ($data['target'] ?? '_self'));
            $textoBotao = trim((string) ($data['texto_botao'] ?? ''));
            $ordem = (int) ($data['ordem'] ?? 0);
            $dataInicio = $data['data_inicio'] ?? null;
            $dataFim = $data['data_fim'] ?? null;
            $ativo = strtoupper(trim((string) ($data['ativo'] ?? 'S'))) === 'S' ? 'S' : 'N';

            if ($id > 0) {
                $sql = 'UPDATE carousel_item SET titulo = :titulo, subtitulo = :subtitulo,
                        imagem = :imagem, link = :link, target = :target,
                        texto_botao = :texto_botao, ordem = :ordem,
                        data_inicio = :data_inicio, data_fim = :data_fim, ativo = :ativo
                        WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':subtitulo', $subtitulo);
                $stmt->bindValue(':imagem', $imagem);
                $stmt->bindValue(':link', $link);
                $stmt->bindValue(':target', $target);
                $stmt->bindValue(':texto_botao', $textoBotao);
                $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
                $stmt->bindValue(':data_inicio', $dataInicio ?: null);
                $stmt->bindValue(':data_fim', $dataFim ?: null);
                $stmt->bindValue(':ativo', $ativo);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO carousel_item (id_carousel, titulo, subtitulo, imagem, link, target, texto_botao, ordem, data_inicio, data_fim, ativo, criado_por)
                    VALUES (:id_carousel, :titulo, :subtitulo, :imagem, :link, :target, :texto_botao, :ordem, :data_inicio, :data_fim, :ativo, :criado_por)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_carousel', $idCarousel, PDO::PARAM_INT);
            $stmt->bindValue(':titulo', $titulo);
            $stmt->bindValue(':subtitulo', $subtitulo);
            $stmt->bindValue(':imagem', $imagem);
            $stmt->bindValue(':link', $link);
            $stmt->bindValue(':target', $target);
            $stmt->bindValue(':texto_botao', $textoBotao);
            $stmt->bindValue(':ordem', $ordem, PDO::PARAM_INT);
            $stmt->bindValue(':data_inicio', $dataInicio ?: null);
            $stmt->bindValue(':data_fim', $dataFim ?: null);
            $stmt->bindValue(':ativo', $ativo);
            $stmt->bindValue(':criado_por', (int) ($data['criado_por'] ?? 1), PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao salvar item: ' . $e->getMessage());
            return 0;
        }
    }

    public function deleteItem(int $id): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $sql = 'DELETE FROM carousel_item WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[CAROUSEL] Erro ao deletar item: ' . $e->getMessage());
        }
    }
}
