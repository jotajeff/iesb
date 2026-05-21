<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class AdminRepository
{
    public function dashboardIndicators(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [
                'total_alunos' => 0,
                'total_cursos' => 0,
                'total_matriculas' => 0,
            ];
        }

        return [
            'total_alunos' => $this->count($pdo, 'SELECT COUNT(*) FROM alunos'),
            'total_cursos' => $this->count($pdo, 'SELECT COUNT(*) FROM cursos_iesb'),
            'total_matriculas' => $this->count($pdo, 'SELECT COUNT(*) FROM matriculas'),
        ];
    }

    public function listCursos(int $limit = 200, string $order = 'desc'): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $direction = strtoupper($order) === 'asc' ? 'ASC' : 'DESC';
        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.tipo_curso,
                       c.ativo, c.imagem_card, c.created_at, t.tipo AS tipo_nome
                FROM cursos_iesb c
                LEFT JOIN cursos_tipo t ON t.id = c.tipo_curso
                ORDER BY c.id ' . $direction . '
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function listCursosDisponiveisHome(int $limit = 6, string $referenceDate = ''): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $referenceDate = $referenceDate !== '' ? $referenceDate : (new \DateTime())->format('Y-m-d');

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.link_ingresso, c.imagem_card, c.tipo_curso, t.tipo AS tipo_nome
                FROM cursos_iesb c
                LEFT JOIN cursos_tipo t ON t.id = c.tipo_curso
                WHERE c.ativo = "S" AND c.curso_calendario > "0000-00-00" AND c.curso_calendario >= :maxDate
                ORDER BY c.curso_calendario ASC, c.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':maxDate', $referenceDate);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function findCursoById(int $id): ?array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return null;
        }

        $sql = 'SELECT c.id, c.nome, c.slug, c.data_curso, c.curso_calendario, c.horario, c.local_curso, c.imagem_card, c.link_ingresso, c.tipo_curso, c.ativo, c.created_at,
                       t.tipo AS tipo_nome
                FROM cursos_iesb c
                LEFT JOIN cursos_tipo t ON t.id = c.tipo_curso
                WHERE c.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateCurso(int $id, array $payload): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb
                SET nome = :nome, slug = :slug, data_curso = :data_curso, curso_calendario = :curso_calendario, horario = :horario, local_curso = :local_curso,
                    imagem_card = :imagem_card, link_ingresso = :link_ingresso, tipo_curso = :tipo_curso, ativo = :ativo
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
        $stmt->bindValue(':tipo_curso', (int) $payload['tipo_curso'], PDO::PARAM_INT);
        $stmt->bindValue(':ativo', (string) $payload['ativo']);
        $stmt->execute();
    }

    public function updateCursoImagem(int $id, string $imagemPath): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb SET imagem_card = :imagem_card WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':imagem_card', $imagemPath);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function listCursosTipos(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, tipo FROM cursos_tipo ORDER BY id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function createCurso(array $payload): int
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return 0;
        }

        $sql = 'INSERT INTO cursos_iesb (nome, slug, data_curso, curso_calendario, horario, local_curso, imagem_card, link_ingresso, tipo_curso, ativo)
                VALUES (:nome, :slug, :data_curso, :curso_calendario, :horario, :local_curso, :imagem_card, :link_ingresso, :tipo_curso, :ativo)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $payload['nome']);
        $stmt->bindValue(':slug', $payload['slug']);
        $stmt->bindValue(':data_curso', $payload['data_curso']);
        $stmt->bindValue(':curso_calendario', $payload['curso_calendario']);
        $stmt->bindValue(':horario', $payload['horario']);
        $stmt->bindValue(':local_curso', $payload['local_curso']);
        $stmt->bindValue(':imagem_card', $payload['imagem_card']);
        $stmt->bindValue(':link_ingresso', $payload['link_ingresso']);
        $stmt->bindValue(':tipo_curso', (int) $payload['tipo_curso'], PDO::PARAM_INT);
        $stmt->bindValue(':ativo', (string) $payload['ativo']);
        $stmt->execute();

        return (int) $pdo->lastInsertId();
    }

    public function listCursosSemSlug(): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, nome, slug
                FROM cursos_iesb
                WHERE slug IS NULL OR slug = ""
                ORDER BY id ASC';
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function updateCursoSlug(int $id, string $slug): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $sql = 'UPDATE cursos_iesb SET slug = :slug WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
    }

    public function cursoSlugExists(string $slug, ?int $ignoreId = null): bool
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return false;
        }

        $sql = 'SELECT id FROM cursos_iesb WHERE slug = :slug';
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

    public function recentLogs(int $limit = 50): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT id, usuario_id, perfil, acao, entidade, entidade_id, descricao, ip, sucesso, created_at
                FROM logs_auditoria
                ORDER BY id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function recentVisits(int $limit = 100): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.endereco_pagina, v.data_visita, v.hora_visita, v.created_at,
                       p.nome AS pagina_nome, p.slug AS pagina_slug
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                ORDER BY v.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function visitsByDayInMonth(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT data_visita, COUNT(*) AS total
                FROM visitas_paginas
                WHERE data_visita LIKE :period
                GROUP BY data_visita
                ORDER BY data_visita ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function visitsInMonthWithPage(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.data_visita,
                       p.nome AS pagina_nome, p.slug AS pagina_slug
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE v.data_visita LIKE :period
                ORDER BY v.id DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function pageVisitTotalsInMonth(int $month, int $year): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT p.nome AS pagina_nome, p.slug AS pagina_slug, COUNT(*) AS total
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE v.data_visita LIKE :period
                GROUP BY p.id, p.nome, p.slug
                ORDER BY total DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function registrarLog(int $usuarioId, string $perfil, string $acao, string $entidade, int $entidadeId, string $descricao, bool $sucesso = true): void
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        $sql = 'INSERT INTO logs_auditoria (usuario_id, perfil, acao, entidade, entidade_id, descricao, ip, sucesso)
                VALUES (:usuario_id, :perfil, :acao, :entidade, :entidade_id, :descricao, :ip, :sucesso)';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':perfil', $perfil);
        $stmt->bindValue(':acao', $acao);
        $stmt->bindValue(':entidade', $entidade);
        $stmt->bindValue(':entidade_id', $entidadeId, PDO::PARAM_INT);
        $stmt->bindValue(':descricao', $descricao);
        $stmt->bindValue(':ip', $ip);
        $stmt->bindValue(':sucesso', $sucesso ? 1 : 0, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function count(PDO $pdo, string $sql): int
    {
        try {
            $result = $pdo->query($sql)->fetchColumn();
            return (int) $result;
        } catch (\Throwable) {
            return 0;
        }
    }
}
