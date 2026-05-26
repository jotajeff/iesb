<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): ?PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        // Try getenv first. If missing (e.g. running in an environment that doesn't load .env),
        // attempt a lightweight .env parser as a fallback so the app works in local setups.
        $host = getenv('DB_HOST') ?: '';
        $port = getenv('DB_PORT') ?: '';
        $dbName = getenv('DB_NAME') ?: '';
        $user = getenv('DB_USER') ?: '';
        $pass = getenv('DB_PASS') ?: '';

        if ($dbName === '' || $user === '' || $host === '') {
            $envPath = dirname(__DIR__, 2) . '/.env';
            if (is_file($envPath)) {
                $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || str_starts_with($line, '#')) {
                        continue;
                    }
                    if (strpos($line, '=') === false) {
                        continue;
                    }
                    [$k, $v] = explode('=', $line, 2);
                    $k = trim($k);
                    $v = trim($v);
                    $v = trim($v, " \t\n\r\"'");
                    putenv(sprintf('%s=%s', $k, $v));
                    $_ENV[$k] = $v;
                    $_SERVER[$k] = $v;
                }

                $host = getenv('DB_HOST') ?: $host;
                $port = getenv('DB_PORT') ?: $port;
                $dbName = getenv('DB_NAME') ?: $dbName;
                $user = getenv('DB_USER') ?: $user;
                $pass = getenv('DB_PASS') ?: $pass;
            }
        } 

        $host = $host ?: '127.0.0.1';
        $port = $port ?: '3306';
        $dbName = $dbName ?: '';
        $user = $user ?: '';
        $pass = $pass ?: '';

        if ($dbName === '' || $user === '') {
            return null;
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

        try {
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            return self::$connection;
        } catch (PDOException) {
            return null;
        }
    }
}
