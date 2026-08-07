<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class CursoRepository
{
    public function list(int $limit = 200, string $order = 'desc', ?int $nivelId = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $direction = strtoupper($order) === 'asc' ? 'ASC' : 'DESC';
        $nivelFilter = ($nivelId !== null && $nivelId > 0) ? (int) $nivelId : null;

        try {
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso,
                            c.ativo, c.exibir_home, c.imagem_card, c.confirmado, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.tipo_curso AS nivel_id, c.created_at,
                            m.nome AS modalidade_nome, s.nome AS segmento_nome, n.nome AS nivel_nome
                     FROM cursos c
                     LEFT JOIN modalidade m ON m.id = c.modalidade
                     LEFT JOIN segmento s ON s.id = c.segmento
                     LEFT JOIN tipo_curso n ON n.id = c.tipo_curso';

            if ($nivelFilter !== null) {
                $sql .= ' WHERE c.tipo_curso = :nivel_id';
            }

            $sql .= ' ORDER BY c.id ' . $direction . ' LIMIT :limit';

            $stmt = $pdo->prepare($sql);
            if ($nivelFilter !== null) {
                $stmt->bindValue(':nivel_id', $nivelFilter, PDO::PARAM_INT);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em list: ' . $e->getMessage());
            return [];
        }
    }

    public function listDisponiveis(int $limit = 200, string $referenceDate = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $referenceDate = $referenceDate !== '' ? $referenceDate : (new \DateTime())->format('Y-m-d');

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.exibir_home, c.confirmado
                FROM cursos c
                WHERE c.ativo = 1 AND c.exibir_home = "S" AND c.curso_calendario IS NOT NULL AND c.curso_calendario >= :maxDate
                ORDER BY c.curso_calendario ASC, c.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':maxDate', $referenceDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listDisponiveisHome(int $limit = 6, string $referenceDate = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $referenceDate = $referenceDate !== '' ? $referenceDate : (new \DateTime())->format('Y-m-d');

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.carga_horaria, c.exibir_home, c.confirmado, c.segmento, c.modalidade, c.tipo_curso, s.nome AS segmento_nome, m.nome AS modalidade_nome, tc.nome AS tipo_curso_nome, tc.slug AS tipo_curso_slug
                FROM cursos c
                LEFT JOIN segmento s ON s.id = c.segmento
                LEFT JOIN modalidade m ON m.id = c.modalidade
                LEFT JOIN tipo_curso tc ON tc.id = c.tipo_curso
                WHERE c.ativo = 1 AND c.exibir_home = "S" AND c.curso_calendario IS NOT NULL AND c.curso_calendario >= :maxDate
                ORDER BY c.curso_calendario ASC, c.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':maxDate', $referenceDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listByNivel(int $nivelId, int $limit = 200): array
    {
        return $this->listByNivelAndSegmento($nivelId, null, $limit);
    }

    public function listByNivelAndSegmento(int $nivelId, ?int $segmentoId = null, int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        if ($nivelId <= 0) {
            return [];
        }

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.confirmado,
                       c.modalidade AS modalidade_id, c.segmento AS segmento_id, m.nome AS modalidade_nome, s.nome AS segmento_nome
                FROM cursos c
                LEFT JOIN modalidade m ON m.id = c.modalidade
                LEFT JOIN segmento s ON s.id = c.segmento
                WHERE c.ativo = 1 AND c.tipo_curso = :nivel_id AND c.curso_calendario >= CURDATE()';

        if ($segmentoId !== null && $segmentoId > 0) {
            $sql .= ' AND c.segmento = :segmento_id';
        }

        $sql .= ' ORDER BY c.curso_calendario ASC, c.id DESC LIMIT :limit';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nivel_id', $nivelId, PDO::PARAM_INT);
        if ($segmentoId !== null && $segmentoId > 0) {
            $stmt->bindValue(':segmento_id', $segmentoId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listAtivos(int $limit = 200): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.confirmado, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.tipo_curso AS nivel_id, m.nome AS modalidade_nome, s.nome AS segmento_nome, n.nome AS nivel_nome
                     FROM cursos c
                     LEFT JOIN modalidade m ON m.id = c.modalidade
                     LEFT JOIN segmento s ON s.id = c.segmento
                     LEFT JOIN tipo_curso n ON n.id = c.tipo_curso
                     WHERE c.ativo = 1
                       AND c.curso_calendario >= CURDATE()
                      ORDER BY c.id DESC
                      LIMIT :limit';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em listAtivos: ' . $e->getMessage());
            return [];
        }
    }

    public function listSemSlug(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, slug
                FROM cursos
                WHERE slug IS NULL OR slug = ""
                ORDER BY id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listIdsComDetalhe(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT id_curso FROM detalhes WHERE ativo = 1';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return is_array($rows) ? array_map('intval', $rows) : [];
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em listIdsComDetalhe: ' . $e->getMessage());
            return [];
        }
    }

    public function listIdsComTurma(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        try {
            $sql = 'SELECT DISTINCT id_curso FROM turmas';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            return is_array($rows) ? array_map('intval', $rows) : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em listIdsComTurma: ' . $e->getMessage());
            return [];
        }
    }

    public function listarCursosTurmas(): array
    {
        try {
            $pdo = Database::connection();
            if (!$pdo instanceof PDO) {
                return [];
            }

            $sql = 'SELECT c.id AS curso_id, c.nome AS curso_nome,
                           t.id AS turma_id, t.nome AS turma_nome,
                           (SELECT COUNT(*) FROM matricula WHERE id_turma = t.id) AS total_inscritos
                    FROM cursos c
                    INNER JOIN turmas t ON t.id_curso = c.id
                    ORDER BY c.nome ASC, t.nome ASC';
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            return is_array($rows) ? $rows : [];
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em listarCursosTurmas: ' . $e->getMessage());
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
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.imagem_card, c.link_ingresso, c.ativo, c.exibir_home, c.confirmado, c.carga_horaria, c.publico_alvo, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.tipo_curso AS nivel_id, c.created_at, s.nome AS segmento_nome
                     FROM cursos c
                     LEFT JOIN segmento s ON s.id = c.segmento
                     WHERE c.id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em findById: ' . $e->getMessage());
            return null;
        }
    }

    public function findBySlug(string $slug): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.imagem_card, c.link_ingresso, c.ativo, c.exibir_home, c.confirmado, c.carga_horaria, c.publico_alvo, c.modalidade AS modalidade_id, c.segmento AS segmento_id, c.tipo_curso AS nivel_id, c.created_at, m.nome AS modalidade_nome, s.nome AS segmento_nome, n.slug AS nivel_slug, n.nome AS nivel_nome
                     FROM cursos c
                     LEFT JOIN modalidade m ON m.id = c.modalidade
                     LEFT JOIN segmento s ON s.id = c.segmento
                     LEFT JOIN tipo_curso n ON n.id = c.tipo_curso
                     WHERE c.slug = :slug';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            $stmt->execute();

            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[CURSOS] Erro em findBySlug: ' . $e->getMessage());
            return null;
        }
    }

    public function create(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO cursos (nome, slug, data_curso, curso_calendario, horario, local_curso, imagem_card, link_ingresso, ativo, exibir_home, confirmado, carga_horaria, modalidade, segmento, tipo_curso, publico_alvo)
                VALUES (:nome, :slug, :data_curso, :curso_calendario, :horario, :local_curso, :imagem_card, :link_ingresso, :ativo, :exibir_home, :confirmado, :carga_horaria, :modalidade_id, :segmento_id, :nivel_id, :publico_alvo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':slug', $payload['slug']);
        $stmt->bindValue(':data_curso', $payload['data_curso']);
        $stmt->bindValue(':curso_calendario', $payload['curso_calendario']);
        $stmt->bindValue(':horario', $payload['horario']);
        $stmt->bindValue(':local_curso', $payload['local_curso']);
        $stmt->bindValue(':imagem_card', $payload['imagem_card']);
        $stmt->bindValue(':link_ingresso', $payload['link_ingresso']);
        $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 0) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':exibir_home', (string) ($payload['exibir_home'] ?? 'N'));
        $stmt->bindValue(':confirmado', (string) ($payload['confirmado'] ?? 'N'));
        $stmt->bindValue(':carga_horaria', $payload['carga_horaria'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':modalidade_id', $payload['modalidade_id'] ?? null, $payload['modalidade_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':segmento_id', $payload['segmento_id'] ?? null, $payload['segmento_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':nivel_id', $payload['nivel_id'] ?? null, $payload['nivel_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':publico_alvo', $payload['publico_alvo'] ?? '');
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos
                SET nome = :nome, slug = :slug, data_curso = :data_curso, curso_calendario = :curso_calendario, horario = :horario, local_curso = :local_curso,
                    imagem_card = :imagem_card, link_ingresso = :link_ingresso, ativo = :ativo, exibir_home = :exibir_home, confirmado = :confirmado,
                    carga_horaria = :carga_horaria, modalidade = :modalidade_id, segmento = :segmento_id, tipo_curso = :nivel_id,
                    publico_alvo = :publico_alvo
                WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':slug', $payload['slug']);
        $stmt->bindValue(':data_curso', $payload['data_curso']);
        $stmt->bindValue(':curso_calendario', $payload['curso_calendario']);
        $stmt->bindValue(':horario', $payload['horario']);
        $stmt->bindValue(':local_curso', $payload['local_curso']);
        $stmt->bindValue(':imagem_card', $payload['imagem_card']);
        $stmt->bindValue(':link_ingresso', $payload['link_ingresso']);
        $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 0) ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':exibir_home', (string) ($payload['exibir_home'] ?? 'N'));
        $stmt->bindValue(':confirmado', (string) ($payload['confirmado'] ?? 'N'));
        $stmt->bindValue(':carga_horaria', $payload['carga_horaria'] ?? 0, PDO::PARAM_INT);
        $stmt->bindValue(':modalidade_id', $payload['modalidade_id'] ?? null, $payload['modalidade_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':segmento_id', $payload['segmento_id'] ?? null, $payload['segmento_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':nivel_id', $payload['nivel_id'] ?? null, $payload['nivel_id'] ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':publico_alvo', $payload['publico_alvo'] ?? '');
        $stmt->execute();
    }

    public function updateImagem(int $id, string $imagemPath): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos SET imagem_card = :imagem_card WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':imagem_card', $imagemPath);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateSlug(int $id, string $slug): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos SET slug = :slug WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $sql = 'SELECT id FROM cursos WHERE slug = :slug';
        if ($ignoreId !== null && $ignoreId > 0) {
            $sql .= ' AND id <> :ignore_id';
        }
        $sql .= ' LIMIT 1';

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignoreId !== null && $ignoreId > 0) {
            $stmt->bindValue(':ignore_id', $ignoreId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function findDetalheByCursoId(int $idCurso): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        try {
            $sql = 'SELECT id, id_curso, detalhe, ativo, created_at FROM detalhes WHERE id_curso = :id_curso LIMIT 1';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $idCurso, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            return $row ?: null;
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em findDetalheByCursoId: ' . $e->getMessage());
            return null;
        }
    }

    public function saveDetalhe(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        try {
            $sql = 'INSERT INTO detalhes (id_curso, detalhe, ativo) VALUES (:id_curso, :detalhe, :ativo)';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_curso', $payload['id_curso'], PDO::PARAM_INT);
            $stmt->bindValue(':detalhe', $payload['detalhe'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 1) ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $pdo->lastInsertId();
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em saveDetalhe: ' . $e->getMessage());
            return 0;
        }
    }

    public function updateDetalhe(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $sql = 'UPDATE detalhes SET detalhe = :detalhe, ativo = :ativo WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':detalhe', $payload['detalhe'] ?? '', PDO::PARAM_STR);
            $stmt->bindValue(':ativo', intval($payload['ativo'] ?? 1) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[DETALHES] Erro em updateDetalhe: ' . $e->getMessage());
        }
    }
}
