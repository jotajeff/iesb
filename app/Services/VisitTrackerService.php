<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;

final class VisitTrackerService
{
    public function track(string $method, string $requestUri): void
    {
        if (strtoupper($method) !== 'GET') {
            return;
        }

        $path = (string) parse_url($requestUri, PHP_URL_PATH);
        if ($path === '') {
            $path = '/';
        }

        if ($this->shouldIgnorePath($path)) {
            return;
        }

        $pdo = Database::connection();
        if (!$pdo instanceof PDO) {
            return;
        }

        try {
            $paginaId = $this->findOrCreatePage($pdo, $path);
            if ($paginaId <= 0) {
                return;
            }

            $ip = $this->resolveIp();
            $today = date('Y-m-d');

            if ($this->alreadyVisitedToday($pdo, $paginaId, $ip, $today)) {
                return;
            }

            $sql = 'INSERT INTO visitas_paginas
                    (pagina_id, ip, user_agent, endereco_pagina, data_visita, hora_visita)
                    VALUES (:pagina_id, :ip, :user_agent, :endereco_pagina, :data_visita, :hora_visita)';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pagina_id' => $paginaId,
                'ip' => $ip,
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 256),
                'endereco_pagina' => $this->fullUrl($requestUri),
                'data_visita' => $today,
                'hora_visita' => date('H:i:s'),
            ]);
        } catch (\Throwable) {
            // Tracking nunca deve derrubar a aplicação.
        }
    }

    private function shouldIgnorePath(string $path): bool
    {
        if (str_starts_with($path, '/assets/')) {
            return true;
        }

        $ignoredPrefixes = [
            '/admin',
            '/aluno',
        ];

        foreach ($ignoredPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        $ignoredExact = ['/favicon.ico'];
        return in_array($path, $ignoredExact, true);
    }

    private function slugFromPath(string $path): string
    {
        if ($path === '/' || $path === '') {
            return 'home';
        }

        return trim($path, '/');
    }

    private function pageNameFromSlug(string $slug): string
    {
        if ($slug === 'home') {
            return 'Home';
        }

        $human = str_replace(['-', '_', '/'], ' ', $slug);
        return mb_convert_case($human, MB_CASE_TITLE, 'UTF-8');
    }

    private function findOrCreatePage(PDO $pdo, string $path): int
    {
        $slug = $this->slugFromPath($path);

        $findStmt = $pdo->prepare('SELECT id FROM paginas WHERE slug = :slug LIMIT 1');
        $findStmt->execute(['slug' => $slug]);
        $id = $findStmt->fetchColumn();

        if ($id !== false) {
            return (int) $id;
        }

        $insertStmt = $pdo->prepare('INSERT INTO paginas (slug, nome, ativa) VALUES (:slug, :nome, 1)');
        $insertStmt->execute([
            'slug' => $slug,
            'nome' => $this->pageNameFromSlug($slug),
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function alreadyVisitedToday(PDO $pdo, int $paginaId, string $ip, string $date): bool
    {
        $sql = 'SELECT id FROM visitas_paginas WHERE pagina_id = :pagina_id AND ip = :ip AND data_visita = :data LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'pagina_id' => $paginaId,
            'ip' => $ip,
            'data' => $date,
        ]);

        return $stmt->fetchColumn() !== false;
    }

    private function resolveIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate) || $candidate === '') {
                continue;
            }

            $ip = trim(explode(',', $candidate)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '0.0.0.0';
    }

    private function fullUrl(string $requestUri): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        return sprintf('%s://%s%s', $scheme, $host, $requestUri);
    }
}
