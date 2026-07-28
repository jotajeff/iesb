<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class NoticiaRepository
{
    public function list(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT n.id, n.titulo, n.slug, n.status, n.destaque, n.data_publicacao,
                           n.created_at, n.id_categoria, cn.nome AS categoria_nome
                    FROM noticia n
                    LEFT JOIN categoria_noticia cn ON cn.id = n.id_categoria
                    ORDER BY n.data_publicacao DESC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao listar notícias: ' . $e->getMessage());
            return [];
        }
    }

    public function listPublicados(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT n.id, n.titulo, n.slug, n.resumo, n.conteudo, n.imagem_capa,
                           n.legenda_imagem, n.autor, n.data_publicacao, n.destaque,
                           n.id_categoria, cn.nome AS categoria_nome
                    FROM noticia n
                    LEFT JOIN categoria_noticia cn ON cn.id = n.id_categoria
                    WHERE n.status = :status
                    ORDER BY n.data_publicacao DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':status', 'publicado');
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao listar notícias publicadas: ' . $e->getMessage());
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
            $sql = 'SELECT id, titulo, slug, resumo, conteudo, imagem_capa, legenda_imagem,
                           categoria, autor, data_publicacao, data_evento, destaque, status,
                           visualizacoes, meta_title, meta_description, created_at, updated_at, id_categoria
                    FROM noticia WHERE id = :id LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao buscar notícia: ' . $e->getMessage());
            return null;
        }
    }

    public function findBySlug(string $slug): ?array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return null;
            }

            $sql = 'SELECT n.id, n.titulo, n.slug, n.resumo, n.conteudo, n.imagem_capa,
                           n.legenda_imagem, n.autor, n.data_publicacao, n.destaque,
                           n.id_categoria, cn.nome AS categoria_nome
                    FROM noticia n
                    LEFT JOIN categoria_noticia cn ON cn.id = n.id_categoria
                    WHERE n.slug = :slug AND n.status = :status
                    LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            $stmt->bindValue(':status', 'publicado');
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao buscar notícia por slug: ' . $e->getMessage());
            return null;
        }
    }

    public function listCategorias(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT id, nome FROM categoria_noticia WHERE ativo = 1 ORDER BY nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao listar categorias: ' . $e->getMessage());
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
            $slug = trim((string) ($data['slug'] ?? ''));
            $resumo = trim((string) ($data['resumo'] ?? ''));
            $conteudo = (string) ($data['conteudo'] ?? '');
            $imagemCapa = trim((string) ($data['imagem_capa'] ?? ''));
            $legendaImagem = trim((string) ($data['legenda_imagem'] ?? ''));
            $autor = trim((string) ($data['autor'] ?? ''));
            $dataPublicacao = (string) ($data['data_publicacao'] ?? date('Y-m-d H:i:s'));
            $dataEvento = $data['data_evento'] ?? null;
            $destaque = (int) ($data['destaque'] ?? 0);
            $status = in_array((string) ($data['status'] ?? 'rascunho'), ['rascunho', 'publicado', 'arquivado'], true)
                ? (string) ($data['status'] ?? 'rascunho') : 'rascunho';
            $metaTitle = trim((string) ($data['meta_title'] ?? ''));
            $metaDescription = trim((string) ($data['meta_description'] ?? ''));
            $idCategoria = $data['id_categoria'] !== null && $data['id_categoria'] !== ''
                ? (int) $data['id_categoria'] : null;

            if ($id > 0) {
                $sql = 'UPDATE noticia SET titulo = :titulo, slug = :slug, resumo = :resumo,
                        conteudo = :conteudo, imagem_capa = :imagem_capa, legenda_imagem = :legenda_imagem,
                        autor = :autor, data_publicacao = :data_publicacao, data_evento = :data_evento,
                        destaque = :destaque, status = :status, meta_title = :meta_title,
                        meta_description = :meta_description, id_categoria = :id_categoria
                        WHERE id = :id';
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->bindValue(':titulo', $titulo);
                $stmt->bindValue(':slug', $slug);
                $stmt->bindValue(':resumo', $resumo);
                $stmt->bindValue(':conteudo', $conteudo);
                $stmt->bindValue(':imagem_capa', $imagemCapa);
                $stmt->bindValue(':legenda_imagem', $legendaImagem);
                $stmt->bindValue(':autor', $autor);
                $stmt->bindValue(':data_publicacao', $dataPublicacao);
                $stmt->bindValue(':data_evento', $dataEvento);
                $stmt->bindValue(':destaque', $destaque, PDO::PARAM_INT);
                $stmt->bindValue(':status', $status);
                $stmt->bindValue(':meta_title', $metaTitle);
                $stmt->bindValue(':meta_description', $metaDescription);
                $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);
                $stmt->execute();
                return $id;
            }

            $sql = 'INSERT INTO noticia (titulo, slug, resumo, conteudo, imagem_capa, legenda_imagem,
                    autor, data_publicacao, data_evento, destaque, status, meta_title, meta_description, id_categoria)
                    VALUES (:titulo, :slug, :resumo, :conteudo, :imagem_capa, :legenda_imagem,
                    :autor, :data_publicacao, :data_evento, :destaque, :status, :meta_title, :meta_description, :id_categoria)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':titulo', $titulo);
            $stmt->bindValue(':slug', $slug);
            $stmt->bindValue(':resumo', $resumo);
            $stmt->bindValue(':conteudo', $conteudo);
            $stmt->bindValue(':imagem_capa', $imagemCapa);
            $stmt->bindValue(':legenda_imagem', $legendaImagem);
            $stmt->bindValue(':autor', $autor);
            $stmt->bindValue(':data_publicacao', $dataPublicacao);
            $stmt->bindValue(':data_evento', $dataEvento);
            $stmt->bindValue(':destaque', $destaque, PDO::PARAM_INT);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':meta_title', $metaTitle);
            $stmt->bindValue(':meta_description', $metaDescription);
            $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao salvar notícia: ' . $e->getMessage());
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
            $sql = 'DELETE FROM noticia WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[NOTICIA] Erro ao deletar notícia: ' . $e->getMessage());
        }
    }
}
