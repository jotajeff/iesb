<?php

declare(strict_types=1);

namespace App\Services\Storage\Providers;

use App\Services\Storage\Contracts\StorageProviderInterface;
use App\Services\Storage\GoogleDriveService;

final class GoogleDriveProvider implements StorageProviderInterface
{
    public function __construct(
        private readonly GoogleDriveService $drive = new GoogleDriveService(),
    ) {
    }

    public function createFolder(string $name, string $parentId = 'root'): string
    {
        return $this->drive->createFolder($name, $parentId);
    }

    public function upload(string $localPath, string $name, string $folderId, string $mimeType = ''): array
    {
        return $this->drive->upload($localPath, $name, $folderId, $mimeType);
    }

    public function download(string $fileId): string
    {
        return $this->drive->download($fileId);
    }

    public function delete(string $fileId): bool
    {
        return $this->drive->delete($fileId);
    }

    public function move(string $fileId, string $folderId): bool
    {
        return $this->drive->move($fileId, $folderId);
    }

    public function rename(string $fileId, string $newName): bool
    {
        return $this->drive->rename($fileId, $newName);
    }

    public function listFiles(string $folderId = 'root'): array
    {
        return $this->drive->listFiles($folderId);
    }

    public function exists(string $fileId): bool
    {
        return $this->drive->exists($fileId);
    }

    public function generateViewLink(string $fileId): string
    {
        return $this->drive->generateViewLink($fileId);
    }

    public function generateDownloadLink(string $fileId): string
    {
        return $this->drive->generateDownloadLink($fileId);
    }
}
