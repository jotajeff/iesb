<?php

declare(strict_types=1);

/**
 * POC - Google Drive API (OAuth 2.0)
 * ===================================
 * Validacao independente da integracao PHP x Google Drive.
 * Nao faz parte do ERP. Nao utiliza MVC, Controllers, Repositories,
 * Services, Banco de Dados ou Sessao do ERP.
 *
 * As credenciais ficam centralizadas na secao CONFIGURACAO abaixo.
 * Tokens sao mantidos apenas em memoria (sessao nativa do PHP),
 * nunca gravados em arquivo.
 */

session_start();

require __DIR__ . '/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

/* ============================================================
   CONFIGURACAO - centralizar tudo aqui
   ============================================================ */

// Credenciais reais vivem em ../material/client_secret_*.json (gitignored).
// Nunca commit este arquivo com segredos. Os placeholders abaixo servem
// apenas de fallback caso o JSON nao esteja presente.
const CLIENT_SECRET_FILE = __DIR__ . '/../material/client_secret_391011534445-kd0a0qg9vcpmptjeeg8ph8rr1kdpqm87.apps.googleusercontent.com.json';
const CLIENT_ID_FALLBACK     = '';
const CLIENT_SECRET_FALLBACK = '';

// REDIRECT_URI:
// - Preencha REDIRECT_URI_OVERRIDE com a URL EXATA registrada no Google
//   Cloud Console (ex.: 'https://www.seudominio.com.br/poc/poc_google_drive.php').
// - Se deixado vazio, o POC calcula automaticamente a partir da request,
//   mas essa URI DINAMICA precisa estar registrada no Console, caso contrario
//   ocorre o erro 400 redirect_uri_mismatch.
const REDIRECT_URI_OVERRIDE = 'https://inteligenciaeducacionalsouzabrazil.com/poc/poc_google_drive.php';

$REDIRECT_URI = REDIRECT_URI_OVERRIDE !== ''
    ? REDIRECT_URI_OVERRIDE
    : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        . strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?');

const SCOPES = [Drive::DRIVE_FILE];
const ROOT_FOLDER_NAME = 'ERP TESTE';

const TEST_FILE      = __DIR__ . '/teste.pdf';
const DOWNLOAD_DIR   = __DIR__ . '/downloads';

/* ============================================================
   HELPERS DE CLIENT
   ============================================================ */

function loadCredentials(): array
{
    if (is_file(CLIENT_SECRET_FILE)) {
        $data = json_decode((string) file_get_contents(CLIENT_SECRET_FILE), true);
        $app = $data['installed'] ?? $data['web'] ?? [];
        if (!empty($app['client_id']) && !empty($app['client_secret'])) {
            return [(string) $app['client_id'], (string) $app['client_secret']];
        }
    }

    return [CLIENT_ID_FALLBACK, CLIENT_SECRET_FALLBACK];
}

function googleClient(): Client
{
    [$clientId, $clientSecret] = loadCredentials();

    if ($clientId === '' || $clientSecret === '') {
        echo renderSimpleError('Credenciais nao encontradas. Coloque o arquivo client_secret_*.json em material/ ou preencha os placeholders em CLIENT_ID_FALLBACK/CLIENT_SECRET_FALLBACK.');
        exit;
    }

    $client = new Client();
    $client->setClientId($clientId);
    $client->setClientSecret($clientSecret);
    $client->setRedirectUri($GLOBALS['REDIRECT_URI']);
    $client->setScopes(SCOPES);
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    $client->setPrompt('consent');

    if (isset($_SESSION['token'])) {
        $client->setAccessToken($_SESSION['token']);
    }

    return $client;
}

function driveService(Client $client): Drive
{
    return new Drive($client);
}

/* ============================================================
   FUNCOES DA POC
   ============================================================ */

/**
 * Conecta ao Google via OAuth 2.0.
 * - Sem code: mostra botao "Conectar Google".
 * - Com code: troca por tokens (refresh + access) e salva na sessao.
 */
function connect(Client $client): void
{
    if (isset($_GET['code'])) {
        try {
            $token = $client->fetchAccessTokenWithAuthCode((string) $_GET['code']);
            if (isset($token['error'])) {
                echo renderError('Erro recebido da API', $token);
                return;
            }
            $_SESSION['token'] = $token;
            header('Location: ' . $GLOBALS['REDIRECT_URI']);
            exit;
        } catch (Exception $e) {
            echo renderExceptionError('Falha ao trocar Authorization Code', $e);
            return;
        }
    }

    if (!isset($_SESSION['token'])) {
        $authUrl = $client->createAuthUrl();
        echo renderConnectButton($authUrl);
        exit;
    }
}

/**
 * Renova o Access Token automaticamente usando o Refresh Token.
 * Retorna o novo access token ou null em caso de falha.
 */
function refreshAccessToken(Client $client): ?string
{
    if (!$client->isAccessTokenExpired()) {
        return (string) $client->getAccessToken()['access_token'];
    }

    $refreshToken = $client->getRefreshToken();
    if (!$refreshToken) {
        echo renderSimpleError('Sem Refresh Token disponivel. Faca login novamente.');
        return null;
    }

    try {
        $newToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
        if (isset($newToken['error'])) {
            echo renderError('Erro ao renovar Access Token', $newToken);
            return null;
        }

        if (isset($newToken['refresh_token'])) {
            $_SESSION['token']['refresh_token'] = $newToken['refresh_token'];
        }
        $_SESSION['token'] = array_merge($_SESSION['token'] ?? [], $newToken);
        $client->setAccessToken($_SESSION['token']);

        return (string) $newToken['access_token'];
    } catch (Exception $e) {
        echo renderExceptionError('Falha ao renovar Access Token', $e);
        return null;
    }
}

/**
 * Teste 01 - Cria a pasta ROOT_FOLDER_NAME ou reutiliza se ja existir.
 */
function createFolder(Drive $service): ?string
{
    try {
        $existing = findFolderByName($service, ROOT_FOLDER_NAME);
        if ($existing !== null) {
            return $existing;
        }

        $folder = new DriveFile([
            'name'     => ROOT_FOLDER_NAME,
            'mimeType' => 'application/vnd.google-apps.folder',
        ]);
        $created = $service->files->create($folder, ['fields' => 'id,name']);
        return (string) $created->getId();
    } catch (Google\Service\Exception $e) {
        echo renderGoogleError('Erro ao criar pasta', $e);
        return null;
    } catch (Exception $e) {
        echo renderExceptionError('Erro ao criar pasta', $e);
        return null;
    }
}

/**
 * Teste 02 - Envia teste.pdf para a pasta informada.
 */
function upload(Drive $service, string $folderId): ?array
{
    if (!is_file(TEST_FILE)) {
        echo renderSimpleError('Arquivo teste.pdf nao encontrado em: ' . TEST_FILE);
        return null;
    }

    try {
        $file = new DriveFile([
            'name'     => 'teste.pdf',
            'parents'  => [$folderId],
        ]);
        $uploaded = $service->files->create($file, [
            'data'        => file_get_contents(TEST_FILE),
            'mimeType'    => mime_content_type(TEST_FILE) ?: 'application/pdf',
            'uploadType'  => 'multipart',
            'fields'      => 'id,name,mimeType,size',
        ]);

        return [
            'name'     => (string) $uploaded->getName(),
            'file_id'  => (string) $uploaded->getId(),
            'mime_type' => (string) $uploaded->getMimeType(),
            'size'     => (int) $uploaded->getSize(),
        ];
    } catch (Google\Service\Exception $e) {
        echo renderGoogleError('Erro ao enviar arquivo', $e);
        return null;
    } catch (Exception $e) {
        echo renderExceptionError('Erro ao enviar arquivo', $e);
        return null;
    }
}

/**
 * Teste 03 - Lista todos os arquivos da pasta informada.
 */
function listFiles(Drive $service, string $folderId): array
{
    try {
        $response = $service->files->listFiles([
            'q'        => sprintf("'%s' in parents and trashed = false", $folderId),
            'fields'   => 'files(id,name,size,mimeType,createdTime)',
            'orderBy'  => 'createdTime desc',
        ]);
        return $response->getFiles();
    } catch (Google\Service\Exception $e) {
        echo renderGoogleError('Erro ao listar arquivos', $e);
        return [];
    } catch (Exception $e) {
        echo renderExceptionError('Erro ao listar arquivos', $e);
        return [];
    }
}

/**
 * Teste 05 - Baixa o arquivo pelo File ID para downloads/.
 */
function download(Drive $service, string $fileId, string $fileName): bool
{
    try {
        if (!is_dir(DOWNLOAD_DIR)) {
            mkdir(DOWNLOAD_DIR, 0755, true);
        }

        $content = $service->files->get($fileId, ['alt' => 'media']);
        $path = DOWNLOAD_DIR . '/' . $fileName;
        file_put_contents($path, $content->getBody()->getContents());

        return is_file($path);
    } catch (Google\Service\Exception $e) {
        echo renderGoogleError('Erro ao baixar arquivo', $e);
        return false;
    } catch (Exception $e) {
        echo renderExceptionError('Erro ao baixar arquivo', $e);
        return false;
    }
}

/**
 * Teste 06 - Exclui o arquivo pelo File ID.
 */
function delete(Drive $service, string $fileId): bool
{
    try {
        $service->files->delete($fileId);
        return true;
    } catch (Google\Service\Exception $e) {
        echo renderGoogleError('Erro ao excluir arquivo', $e);
        return false;
    } catch (Exception $e) {
        echo renderExceptionError('Erro ao excluir arquivo', $e);
        return false;
    }
}

/* ============================================================
   HELPERS DE BUSCA
   ============================================================ */

function findFolderByName(Drive $service, string $name): ?string
{
    $response = $service->files->listFiles([
        'q'      => sprintf("name = '%s' and mimeType = 'application/vnd.google-apps.folder' and trashed = false", $name),
        'fields' => 'files(id,name)',
    ]);

    $files = $response->getFiles();
    if (count($files) > 0) {
        return (string) $files[0]->getId();
    }

    return null;
}

/* ============================================================
   RENDERIZACAO (HTML simples, sem framework)
   ============================================================ */

function renderConnectButton(string $authUrl): void
{
    $redirect = $GLOBALS['REDIRECT_URI'];
    $registered = loadRegisteredRedirectUris();
    $isLocalhost = str_contains($redirect, '://localhost') || str_contains($redirect, '://127.0.0.1');
    $registeredOk = in_array($redirect, $registered, true) || ($isLocalhost && in_array('http://localhost', $registered, true));

    $html = '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">';
    $html .= '<title>POC - Google Drive</title></head><body>';
    $html .= '<h1>POC - Google Drive API</h1>';
    $html .= '<p>Validação de integração PHP x Google Drive via OAuth 2.0.</p>';
    $html .= '<p>Redirect URI em uso: <code>' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') . '</code></p>';

    if (!$registeredOk) {
        $html .= '<div style="border:1px solid #dc3545;padding:10px;margin:10px 0;color:#dc3545;">';
        $html .= '<strong>Atenção:</strong> esta Redirect URI não está registrada no Google Cloud Console '
            . '(registradas: <code>' . htmlspecialchars(implode(', ', $registered), ENT_QUOTES, 'UTF-8') . '</code>). '
            . 'Acesse o Console > API e Serviços > Credenciais > seu client OAuth e adicione exatamente:<br>'
            . '<code>' . htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') . '</code>';
        $html .= '</div>';
    }

    $html .= '<p><a href="' . htmlspecialchars($authUrl, ENT_QUOTES, 'UTF-8') . '">Conectar Google</a></p>';
    $html .= '</body></html>';
    echo $html;
}

function loadRegisteredRedirectUris(): array
{
    if (!is_file(CLIENT_SECRET_FILE)) {
        return [];
    }

    $data = json_decode((string) file_get_contents(CLIENT_SECRET_FILE), true);
    $app = $data['installed'] ?? $data['web'] ?? [];

    return array_values(array_filter(
        array_map(
            static fn ($uri) => (string) $uri,
            $app['redirect_uris'] ?? []
        ),
        static fn (string $uri) => $uri !== ''
    ));
}

function renderGoogleError(string $title, Google\Service\Exception $e): void
{
    $message = $e->getMessage();
    $httpCode = (int) $e->getCode();
    echo renderBox('error', $title, "Código HTTP: {$httpCode}<br>Mensagem: " . htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
}

function renderExceptionError(string $title, Exception $e): void
{
    echo renderBox('error', $title, 'Mensagem: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

function renderSimpleError(string $message): void
{
    echo renderBox('error', 'Erro', htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
}

function renderError(string $title, array $data): void
{
    $details = isset($data['error_description'])
        ? htmlspecialchars((string) $data['error_description'], ENT_QUOTES, 'UTF-8')
        : htmlspecialchars((string) ($data['error'] ?? 'Erro desconhecido'), ENT_QUOTES, 'UTF-8');
    echo renderBox('error', $title, $details);
}

function renderBox(string $type, string $title, string $content): string
{
    $color = $type === 'error' ? '#dc3545' : '#0d6efd';
    return '<div style="border:1px solid ' . $color . ';padding:10px;margin:10px 0;color:' . $color . ';">'
        . '<strong>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . ':</strong> ' . $content . '</div>';
}

function renderResult(string $label, bool $ok, string $detail = ''): string
{
    $status = $ok ? 'OK' : 'FALHOU';
    $color = $ok ? '#198754' : '#dc3545';
    return '<tr><td>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td>'
        . '<td style="color:' . $color . ';font-weight:bold;">' . $status . '</td>'
        . '<td>' . htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') . '</td></tr>';
}

/* ============================================================
   FLUXO PRINCIPAL
   ============================================================ */

$client = googleClient();
connect($client);

$accessToken = refreshAccessToken($client);
if ($accessToken === null) {
    exit;
}

$service = driveService($client);

/* Exibicao dos tokens APENAS durante os testes (nunca em arquivo). */
$accountInfo = $client->getOAuth2Service()->userinfo->get();
$accountEmail = (string) $accountInfo->getEmail();
$refreshToken = (string) $client->getRefreshToken();

$results = [];
$folderId = null;
$fileId = null;

/* Teste 01 - Criar pasta (ou reutilizar existente) */
$folderId = createFolder($service);
if ($folderId !== null) {
    $results[] = renderResult('Criar Pasta', true, "Folder ID: {$folderId}");
} else {
    $results[] = renderResult('Criar Pasta', false, 'Falha na criação da pasta');
}

/* Teste 02 - Upload do teste.pdf */
$uploadInfo = null;
if ($folderId !== null) {
    $uploadInfo = upload($service, $folderId);
    if ($uploadInfo !== null) {
        $fileId = $uploadInfo['file_id'];
        $results[] = renderResult('Upload', true,
            "Nome: {$uploadInfo['name']} | File ID: {$fileId} | Mime: {$uploadInfo['mime_type']} | Tamanho: {$uploadInfo['size']} bytes");
    } else {
        $results[] = renderResult('Upload', false, 'Falha no upload');
    }
} else {
    $results[] = renderResult('Upload', false, 'Pasta não disponível');
}

/* Teste 03 - Listar arquivos da pasta */
$files = ($folderId !== null) ? listFiles($service, $folderId) : [];
if (!empty($files) || $folderId !== null) {
    $fileCount = count($files);
    $results[] = renderResult('Listagem', true, "{$fileCount} arquivo(s) na pasta");
} else {
    $results[] = renderResult('Listagem', false, 'Falha na listagem');
}

/* Teste 04 - Links de visualização e download */
$viewLink = $fileId ? 'https://drive.google.com/file/d/' . $fileId . '/view' : '';
$downloadLink = $fileId ? 'https://drive.google.com/uc?export=download&id=' . $fileId : '';
$results[] = renderResult('Links (View/Download)', $fileId !== null,
    $fileId !== null ? '<a href="' . htmlspecialchars($viewLink, ENT_QUOTES, 'UTF-8') . '" target="_blank">Ver</a> | <a href="' . htmlspecialchars($downloadLink, ENT_QUOTES, 'UTF-8') . '" target="_blank">Baixar</a>' : '');

/* Teste 05 - Baixar teste.pdf */
$downloaded = ($fileId !== null) ? download($service, $fileId, 'teste.pdf') : false;
$results[] = renderResult('Download', $downloaded, $downloaded ? 'Salvo em downloads/teste.pdf' : 'Falha no download');

/* Teste 06 - Excluir teste.pdf (pedir confirmacao antes) */
$deleteStatus = null;
if (isset($_POST['confirm_delete'])) {
    $deleteStatus = delete($service, (string) $_POST['file_id']);
}

/* ============================================================
   OUTPUT
   ============================================================ */

$allOk = $fileId !== null && $downloaded && ($deleteStatus === null || $deleteStatus === true);
$deleteNotRun = $deleteStatus === null;

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>POC - Google Drive API</title>
<style>
body { font-family: Arial, Helvetica, sans-serif; margin: 40px auto; max-width: 900px; padding: 0 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px 10px; text-align: left; font-size: 14px; }
th { background: #f0f0f0; }
h1 { font-size: 22px; }
h2 { font-size: 18px; margin-top: 30px; }
pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 13px; }
</style>
</head>
<body>

<h1>POC - Google Drive API</h1>

<h2>Credenciais</h2>
<table>
<tr><th>Conta Google</th><td><?= htmlspecialchars($accountEmail, ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><th>Refresh Token</th><td><?= htmlspecialchars($refreshToken !== '' ? substr($refreshToken, 0, 30) . '...' : 'N/D', ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><th>Access Token</th><td><?= htmlspecialchars($accessToken !== '' ? substr($accessToken, 0, 30) . '...' : 'N/D', ENT_QUOTES, 'UTF-8') ?></td></tr>
</table>

<h2>Resultado dos Testes</h2>
<table>
<tr><th>Item</th><th>Status</th><th>Detalhe</th></tr>
<tr><td>Google OAuth</td><td style="color:#198754;font-weight:bold;">OK</td><td>Conectado como <?= htmlspecialchars($accountEmail, ENT_QUOTES, 'UTF-8') ?></td></tr>
<tr><td>Refresh Token</td><td style="color:#198754;font-weight:bold;">OK</td><td><?= $refreshToken !== '' ? 'Renovação automática ativa' : 'N/D' ?></td></tr>
<tr><td>Access Token</td><td style="color:#198754;font-weight:bold;">OK</td><td>Token renovado automaticamente</td></tr>
<?= implode('', $results) ?>
</table>

<?php if ($deleteNotRun && $fileId !== null): ?>
<h2>Teste 06 - Confirmar exclusão?</h2>
<form method="post" action="<?= htmlspecialchars($GLOBALS['REDIRECT_URI'], ENT_QUOTES, 'UTF-8') ?>">
  <input type="hidden" name="file_id" value="<?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>">
  <p>Excluir <strong>teste.pdf</strong> (File ID: <?= htmlspecialchars($fileId, ENT_QUOTES, 'UTF-8') ?>) do Google Drive?</p>
  <button type="submit" name="confirm_delete" value="1">Sim</button>
  <button type="submit" name="confirm_delete" value="0">Não</button>
</form>
<?php elseif ($deleteStatus === true): ?>
  <div style="border:1px solid #198754;padding:10px;margin:10px 0;color:#198754;"><strong>Exclusão:</strong> OK - teste.pdf excluído do Google Drive.</div>
<?php elseif ($deleteStatus === false): ?>
  <div style="border:1px solid #dc3545;padding:10px;margin:10px 0;color:#dc3545;"><strong>Exclusão:</strong> FALHOU</div>
<?php endif; ?>

<h2>Resumo Final</h2>
<div style="border:1px solid #000;padding:10px;margin-top:10px;">
<pre>
==============================
Google OAuth ............ OK
Refresh Token ........... OK
Access Token ............ OK
Criar Pasta ............. <?= $folderId !== null ? 'OK' : 'FALHOU' ?>

Upload .................. <?= $uploadInfo !== null ? 'OK' : 'FALHOU' ?>

Listagem ................ <?= $folderId !== null ? 'OK' : 'FALHOU' ?>

Download ................ <?= $downloaded ? 'OK' : 'FALHOU' ?>

Exclusão ................ <?= $deleteStatus === null ? 'PENDENTE' : ($deleteStatus ? 'OK' : 'FALHOU') ?>

==============================
<?= ($allOk && $deleteStatus === true) ? 'POC APROVADA' : 'POC NAO APROVADA' ?>

==============================
</pre>
</div>

</body>
</html>
