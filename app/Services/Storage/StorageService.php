<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Repositories\DocumentoRepository;
use App\Repositories\IntegracaoGoogleRepository;
use App\Repositories\LogRepository;
use App\Services\Storage\Contracts\StorageProviderInterface;
use App\Services\Storage\Providers\GoogleDriveProvider;
use Throwable;

final class StorageService
{
    public const GROUP_ALUNOS = 1;
    public const GROUP_PROFESSORES = 2;
    public const GROUP_MATERIAIS = 3;
    public const GROUP_FINANCEIRO = 4;
    public const GROUP_CONTRATOS = 5;
    public const GROUP_CERTIFICADOS = 6;

    public function __construct(
        private readonly StorageProviderInterface $provider = new GoogleDriveProvider(),
        private readonly IntegracaoGoogleRepository $integracaoRepository = new IntegracaoGoogleRepository(),
        private readonly DocumentoRepository $documentoRepository = new DocumentoRepository(),
        private readonly LogRepository $logRepository = new LogRepository(),
    ) {
    }

    public function isConnected(): bool
    {
        $config = $this->integracaoRepository->findActive();
        if ($config === null) {
            return false;
        }

        $refreshToken = (string) ($config['refresh_token'] ?? '');
        if ($refreshToken === '') {
            return false;
        }

        return true;
    }

    public function connectionInfo(): ?array
    {
        return $this->integracaoRepository->findActive();
    }

    public function connectUrl(): string
    {
        return (new GoogleOAuthService())->authUrl();
    }

    public function callback(string $code): array
    {
        return (new GoogleOAuthService())->exchangeCode($code);
    }

    public function disconnect(): void
    {
        (new GoogleOAuthService())->disconnect();
    }

    public function createFolder(string $name, string $parentId = 'root'): string
    {
        return $this->run('criar_pasta', $name, static function () use ($name, $parentId): string {
            return $this->provider->createFolder($name, $parentId);
        });
    }

    /**
     * Garante a estrutura padrao de pastas na raiz do Drive.
     * Retorna mapa: ['alunos' => id, 'professores' => id, ...].
     */
    public function ensureStructure(): array
    {
        return $this->run('estrutura_pastas', 'raiz', function (): array {
            $config = $this->integracaoRepository->findActive();
            $rootFolderId = (string) ($config['root_folder_id'] ?? '');

            if ($rootFolderId === '') {
                $rootFolderId = $this->provider->createFolder('Documentos Acadêmicos', 'root');
                $configId = (int) ($config['id'] ?? 0);
                if ($configId > 0) {
                    $this->integracaoRepository->saveRootFolder($configId, $rootFolderId, 'Documentos Acadêmicos');
                }
            }

            return [
                'root' => $rootFolderId,
                'alunos' => $this->provider->createFolder('Alunos', $rootFolderId),
                'professores' => $this->provider->createFolder('Professores', $rootFolderId),
                'materiais' => $this->provider->createFolder('Materiais', $rootFolderId),
                'financeiro' => $this->provider->createFolder('Financeiro', $rootFolderId),
                'contratos' => $this->provider->createFolder('Contratos', $rootFolderId),
                'certificados' => $this->provider->createFolder('Certificados', $rootFolderId),
            ];
        });
    }

    /**
     * Cria (ou reutiliza) a pasta individual do registro: "000001-Nome".
     */
    public function ensureRegistroFolder(int $group, string $matricula, string $nome): string
    {
        $structure = $this->ensureStructure();
        $groupKey = $this->groupKey($group);
        $parentId = (string) ($structure[$groupKey] ?? $structure['root']);

        $folderName = $this->registroFolderName($matricula, $nome);
        return $this->provider->createFolder($folderName, $parentId);
    }

    public function upload(array $file, int $idGrupo, int $idRegistro, int $idTipo, ?string $folderId = null, ?string $nomeDrive = null, string $status = 'enviado'): array
    {
        $localPath = (string) ($file['tmp_name'] ?? '');
        $originalName = (string) ($file['name'] ?? '');

        if ($localPath === '' || !is_file($localPath)) {
            throw new StorageException('Arquivo temporário inválido.');
        }

        if ($originalName === '') {
            $originalName = 'documento.pdf';
        }

        if ($folderId === null || $folderId === '') {
            $folderId = $this->defaultFolderForGroup($idGrupo);
        }

        $nameDrive = $nomeDrive !== null && $nomeDrive !== ''
            ? $nomeDrive
            : $this->driveName($idRegistro, $idTipo, $originalName);
        $mimeType = (string) ($file['type'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        $result = $this->run('upload', $originalName, function () use ($localPath, $nameDrive, $folderId, $mimeType): array {
            return $this->provider->upload($localPath, $nameDrive, $folderId, $mimeType);
        });

        $documentoId = $this->documentoRepository->create([
            'id_grupo' => $idGrupo,
            'id_registro' => $idRegistro,
            'id_tipo' => $idTipo,
            'nome_original' => $originalName,
            'nome_drive' => $nameDrive,
            'folder_id' => $folderId,
            'mime_type' => $result['mime_type'],
            'tamanho' => $result['size'] > 0 ? $result['size'] : $size,
            'file_id' => $result['file_id'],
            'status' => $status,
        ]);

        if (is_file($localPath)) {
            @unlink($localPath);
        }

        return [
            'id' => $documentoId,
            'file_id' => $result['file_id'],
            'nome_original' => $originalName,
            'nome_drive' => $nameDrive,
            'mime_type' => $result['mime_type'],
            'tamanho' => $result['size'],
            'status' => $status,
        ];
    }

    public function download(int $documentoId): string
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        $fileId = (string) $documento['file_id'];
        return $this->run('download', (string) $documento['nome_original'], function () use ($fileId): string {
            return $this->provider->download($fileId);
        });
    }

    public function downloadByFileId(string $fileId): string
    {
        return $this->run('download', $fileId, function () use ($fileId): string {
            return $this->provider->download($fileId);
        });
    }

    public function delete(int $documentoId): bool
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        $fileId = (string) $documento['file_id'];
        $this->run('excluir', (string) $documento['nome_original'], function () use ($fileId): bool {
            return $this->provider->delete($fileId);
        });
        $this->documentoRepository->softDelete($documentoId);

        return true;
    }

    public function rename(int $documentoId, string $newName): bool
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        $fileId = (string) $documento['file_id'];
        $this->run('renomear', $newName, function () use ($fileId, $newName): bool {
            return $this->provider->rename($fileId, $newName);
        });

        $this->documentoRepository->update($documentoId, ['nome_drive' => $newName]);

        return true;
    }

    public function list(?int $idGrupo = null, ?int $idRegistro = null, ?int $idTipo = null): array
    {
        if ($idGrupo === null || $idRegistro === null) {
            return [];
        }

        return $this->documentoRepository->listByRegistro($idGrupo, $idRegistro, $idTipo);
    }

    public function listFolder(string $folderId): array
    {
        return $this->run('listar', $folderId, function () use ($folderId): array {
            return $this->provider->listFiles($folderId);
        });
    }

    public function exists(int $documentoId): bool
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            return false;
        }

        return $this->provider->exists((string) $documento['file_id']);
    }

    public function move(int $documentoId, string $folderId): bool
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        $fileId = (string) $documento['file_id'];
        $this->run('mover', (string) $documento['nome_original'], function () use ($fileId, $folderId): bool {
            return $this->provider->move($fileId, $folderId);
        });

        return true;
    }

    public function generateViewLink(int $documentoId): string
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        return $this->provider->generateViewLink((string) $documento['file_id']);
    }

    public function generateViewLinkByFileId(string $fileId): string
    {
        return $this->provider->generateViewLink($fileId);
    }

    public function generateDownloadLinkByFileId(string $fileId): string
    {
        return $this->provider->generateDownloadLink($fileId);
    }

    /**
     * Torna o documento publico no Drive (qualquer pessoa com o link pode
     * visualizar/baixar, sem estar logado no Google).
     */
    public function sharePublic(int $documentoId): bool
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        $fileId = (string) $documento['file_id'];
        return $this->run('compartilhar', (string) $documento['nome_original'], function () use ($fileId): bool {
            return $this->provider->sharePublic($fileId);
        });
    }

    public function sharePublicByFileId(string $fileId): bool
    {
        return $this->run('compartilhar', $fileId, function () use ($fileId): bool {
            return $this->provider->sharePublic($fileId);
        });
    }

    public function generateDownloadLink(int $documentoId): string
    {
        $documento = $this->documentoRepository->findById($documentoId);
        if ($documento === null) {
            throw new StorageException('Documento não encontrado.');
        }

        return $this->provider->generateDownloadLink((string) $documento['file_id']);
    }

    private function run(string $acao, string $entidade, callable $callback): mixed
    {
        $start = microtime(true);
        try {
            $result = $callback();
            $this->log($acao, $entidade, true, $start);
            return $result;
        } catch (Throwable $e) {
            $this->log($acao, $entidade, false, $start, $e->getMessage());
            $this->handleApiError($e);
            throw new StorageException($e->getMessage(), 0, $e);
        }
    }

    private function log(string $acao, string $entidade, bool $sucesso, float $start, string $erro = ''): void
    {
        $tempo = round((microtime(true) - $start) * 1000);
        $descricao = $sucesso
            ? "Storage: {$acao} em {$entidade} ({$tempo}ms)"
            : "Storage: falha em {$acao} em {$entidade} ({$tempo}ms) - {$erro}";

        $this->logRepository->registrar(0, 'sistema', $acao, 'storage', 0, $descricao, $sucesso);
    }

    private function handleApiError(Throwable $e): void
    {
        $message = $e->getMessage();
        $code = (int) $e->getCode();

        $authErrors = [
            'invalid_grant',
            'invalid_token',
            'unauthorized',
            'access_denied',
        ];
        $isAuth = in_array($code, [401, 403], true);
        foreach ($authErrors as $needle) {
            if (str_contains(strtolower($message), $needle)) {
                $isAuth = true;
                break;
            }
        }

        if ($isAuth) {
            $config = $this->integracaoRepository->findActive();
            $configId = (int) ($config['id'] ?? 0);
            if ($configId > 0) {
                $this->integracaoRepository->setDesconectado($configId);
            }
        }

        error_log('[STORAGE] Erro na API Google: ' . $message);
    }

    private function groupKey(int $group): string
    {
        return match ($group) {
            self::GROUP_ALUNOS => 'alunos',
            self::GROUP_PROFESSORES => 'professores',
            self::GROUP_MATERIAIS => 'materiais',
            self::GROUP_FINANCEIRO => 'financeiro',
            self::GROUP_CONTRATOS => 'contratos',
            self::GROUP_CERTIFICADOS => 'certificados',
            default => 'root',
        };
    }

    private function defaultFolderForGroup(int $group): string
    {
        $structure = $this->ensureStructure();
        $key = $this->groupKey($group);

        return (string) ($structure[$key] ?? $structure['root']);
    }

    private function registroFolderName(string $matricula, string $nome): string
    {
        $matricula = preg_replace('/[^0-9]/', '', $matricula);
        $matricula = $matricula !== '' ? $matricula : '000000';
        $matricula = str_pad($matricula, 6, '0', STR_PAD_LEFT);

        $nome = preg_replace('/[^A-Za-z0-9áàâãéêíóôõúüçÁÀÂÃÉÊÍÓÔÕÚÜÇ\s-]/u', '', $nome);
        $nome = trim((string) $nome);
        $nome = $nome !== '' ? $nome : 'SemNome';

        return sprintf('%s-%s', $matricula, $nome);
    }

    private function driveName(int $idRegistro, int $idTipo, string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $base = pathinfo($originalName, PATHINFO_FILENAME);
        $safe = preg_replace('/[^A-Za-z0-9_-]/', '_', $base);

        return sprintf('%06d_%04d_%s.%s', $idRegistro, $idTipo, $safe, $extension);
    }
}
