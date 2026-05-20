<?php

declare(strict_types=1);

namespace App\Repositories;

abstract class JsonRepository
{
    public function __construct(private readonly string $filePath)
    {
    }

    protected function allData(): array
    {
        if (!is_file($this->filePath)) {
            return [];
        }

        $json = file_get_contents($this->filePath);
        if ($json === false || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    protected function saveAllData(array $rows): void
    {
        file_put_contents(
            $this->filePath,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function nextId(array $rows): int
    {
        if ($rows === []) {
            return 1;
        }

        $ids = array_column($rows, 'id');
        return (int) max($ids) + 1;
    }
}
