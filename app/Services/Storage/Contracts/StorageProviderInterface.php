<?php

declare(strict_types=1);

namespace App\Services\Storage\Contracts;

interface StorageProviderInterface
{
    public function createFolder(string $name, string $parentId = 'root'): string;

    public function upload(string $localPath, string $name, string $folderId, string $mimeType = ''): array;

    public function download(string $fileId): string;

    public function delete(string $fileId): bool;

    public function move(string $fileId, string $folderId): bool;

    public function rename(string $fileId, string $newName): bool;

    public function listFiles(string $folderId = 'root'): array;

    public function exists(string $fileId): bool;

    public function generateViewLink(string $fileId): string;

    public function generateDownloadLink(string $fileId): string;
}
