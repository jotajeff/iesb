<?php

declare(strict_types=1);

namespace App\Services\Storage;

use Google\Client;
use Google\Exception as GoogleException;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Throwable;

final class GoogleDriveService
{
    public function __construct(
        private readonly GoogleOAuthService $oauth = new GoogleOAuthService(),
    ) {
    }

    public function createFolder(string $name, string $parentId = 'root'): string
    {
        $service = $this->service();

        $existing = $this->findFolderByName($service, $name, $parentId);
        if ($existing !== null) {
            return $existing;
        }

        $folder = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);
        $created = $service->files->create($folder, ['fields' => 'id,name']);

        return (string) $created->getId();
    }

    public function upload(string $localPath, string $name, string $folderId, string $mimeType = ''): array
    {
        if (!is_file($localPath)) {
            throw new GoogleException("Arquivo temporário não encontrado: {$localPath}");
        }

        $service = $this->service();

        $file = new DriveFile([
            'name' => $name,
            'parents' => [$folderId],
        ]);
        $uploaded = $service->files->create($file, [
            'data' => (string) file_get_contents($localPath),
            'mimeType' => $mimeType !== '' ? $mimeType : 'application/octet-stream',
            'uploadType' => 'multipart',
            'fields' => 'id,name,mimeType,size',
        ]);

        return [
            'file_id' => (string) $uploaded->getId(),
            'name' => (string) $uploaded->getName(),
            'mime_type' => (string) $uploaded->getMimeType(),
            'size' => (int) $uploaded->getSize(),
        ];
    }

    public function download(string $fileId): string
    {
        $service = $this->service();
        $response = $service->files->get($fileId, ['alt' => 'media']);

        return (string) $response->getBody()->getContents();
    }

    public function delete(string $fileId): bool
    {
        $service = $this->service();
        $service->files->delete($fileId);
        return true;
    }

    public function move(string $fileId, string $folderId): bool
    {
        $service = $this->service();

        $file = $service->files->get($fileId, ['fields' => 'parents']);
        $previousParents = implode(',', (array) $file->getParents());

        $service->files->update($fileId, new DriveFile(), [
            'addParents' => $folderId,
            'removeParents' => $previousParents,
            'fields' => 'id,parents',
        ]);

        return true;
    }

    public function rename(string $fileId, string $newName): bool
    {
        $service = $this->service();
        $service->files->update($fileId, new DriveFile(['name' => $newName]));
        return true;
    }

    public function listFiles(string $folderId = 'root'): array
    {
        $service = $this->service();

        $response = $service->files->listFiles([
            'q' => sprintf("'%s' in parents and trashed = false", $folderId),
            'fields' => 'files(id,name,size,mimeType,createdTime,webViewLink)',
            'orderBy' => 'createdTime desc',
        ]);

        $files = $response->getFiles();
        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'file_id' => (string) $file->getId(),
                'name' => (string) $file->getName(),
                'mime_type' => (string) $file->getMimeType(),
                'size' => (int) $file->getSize(),
                'created_at' => (string) $file->getCreatedTime(),
                'link' => (string) $file->getWebViewLink(),
            ];
        }

        return $result;
    }

    public function exists(string $fileId): bool
    {
        try {
            $service = $this->service();
            $service->files->get($fileId, ['fields' => 'id']);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function generateViewLink(string $fileId): string
    {
        return 'https://drive.google.com/file/d/' . $fileId . '/view';
    }

    public function generateDownloadLink(string $fileId): string
    {
        return 'https://drive.google.com/uc?export=download&id=' . $fileId;
    }

    private function service(): Drive
    {
        $client = $this->authenticatedClient();
        return new Drive($client);
    }

    private function authenticatedClient(): Client
    {
        $client = $this->oauth->client();
        $accessToken = $this->oauth->accessToken();

        if ($accessToken === null) {
            throw new GoogleException('Integração com Google Drive não está conectada.');
        }

        $client->setAccessToken(['access_token' => $accessToken]);
        return $client;
    }

    private function findFolderByName(Drive $service, string $name, string $parentId): ?string
    {
        $response = $service->files->listFiles([
            'q' => sprintf(
                "name = '%s' and '%s' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
                $this->escapeQuery($name),
                $parentId
            ),
            'fields' => 'files(id,name)',
        ]);

        $files = $response->getFiles();
        if (count($files) > 0) {
            return (string) $files[0]->getId();
        }

        return null;
    }

    private function escapeQuery(string $value): string
    {
        return str_replace("'", "\\'", $value);
    }
}
