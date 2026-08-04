<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class VisitaRepository
{
    public function byDate(string $date, int $limit = 100): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.referer, v.utm_source, v.utm_medium, v.utm_campaign,
                       v.endereco_pagina, v.data_visita, v.hora_visita, v.created_at,
                       p.nome AS pagina_nome, p.slug AS pagina_slug
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE v.data_visita = :data_visita
                ORDER BY v.id DESC
                LIMIT :limit';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':data_visita', $date);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function recent(int $limit = 100): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT v.id, v.ip, v.user_agent, v.referer, v.utm_source, v.utm_medium, v.utm_campaign,
                       v.endereco_pagina, v.data_visita, v.hora_visita, v.created_at,
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

    public function byDayInMonth(int $month, int $year): array
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

    public function inMonthWithPage(int $month, int $year): array
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

    public function pageTotalsInMonth(int $month, int $year): array
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

    public function pageTotalsBySlugPrefix(string $prefix): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $sql = 'SELECT p.nome AS pagina_nome, p.slug AS pagina_slug, COUNT(*) AS total
                FROM visitas_paginas v
                INNER JOIN paginas p ON p.id = v.pagina_id
                WHERE p.slug LIKE :prefix
                GROUP BY p.id, p.nome, p.slug
                ORDER BY total DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':prefix', $prefix . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function refererStats(?int $month = null, ?int $year = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');
        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT referer, COUNT(*) AS total
                FROM visitas_paginas
                WHERE data_visita LIKE :period AND referer != \'\'
                GROUP BY referer
                ORDER BY total DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function utmStats(?int $month = null, ?int $year = null): array
    {
        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return [];
        }

        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');
        $monthStr = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $period = sprintf('%04d-%s', $year, $monthStr);

        $sql = 'SELECT utm_source, utm_medium, utm_campaign, COUNT(*) AS total
                FROM visitas_paginas
                WHERE data_visita LIKE :period AND utm_source != \'\'
                GROUP BY utm_source, utm_medium, utm_campaign
                ORDER BY total DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':period', $period . '%');
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }
}
