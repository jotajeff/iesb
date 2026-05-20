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
            'total_cursos' => $this->count($pdo, 'SELECT COUNT(*) FROM cursos'),
            'total_matriculas' => $this->count($pdo, 'SELECT COUNT(*) FROM matriculas'),
        ];
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
