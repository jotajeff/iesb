<?php

declare(strict_types=1);

namespace App\Services\Storage;

use App\Repositories\IntegracaoGoogleRepository;
use Google\Client;
use Google\Exception as GoogleException;
use Google\Service\Drive;
use Google\Service\Oauth2;
use Throwable;

final class GoogleOAuthService
{
    public function __construct(
        private readonly IntegracaoGoogleRepository $repository = new IntegracaoGoogleRepository(),
    ) {
    }

    public function client(): Client
    {
        $client = new Client();
        $client->setClientId((string) getenv('GOOGLE_CLIENT_ID'));
        $client->setClientSecret((string) getenv('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri((string) getenv('GOOGLE_REDIRECT_URI'));
        $client->setScopes([Drive::DRIVE_FILE, Oauth2::OPENID, Oauth2::USERINFO_EMAIL]);
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $client->setPrompt('consent');

        $config = $this->repository->findActive();
        $refreshToken = $config['refresh_token'] ?? '';

        if (is_string($refreshToken) && $refreshToken !== '') {
            $client->getOAuth2Service()->setRefreshToken($refreshToken);
        }

        return $client;
    }

    public function authUrl(): string
    {
        return $this->client()->createAuthUrl();
    }

    public function exchangeCode(string $code): array
    {
        $client = $this->client();
        $token = $client->fetchAccessTokenWithAuthCode($code);

        if (isset($token['error'])) {
            throw new GoogleException((string) ($token['error_description'] ?? $token['error']));
        }

        $refreshToken = (string) ($token['refresh_token'] ?? $client->getRefreshToken());
        if ($refreshToken === '') {
            throw new GoogleException('Nenhum refresh token retornado pelo Google.');
        }

        $config = $this->repository->findActive();
        $configId = (int) ($config['id'] ?? 0);

        if ($configId <= 0) {
            $configId = $this->repository->create([
                'nome' => 'Google Drive',
                'client_id' => (string) getenv('GOOGLE_CLIENT_ID'),
                'client_secret' => (string) getenv('GOOGLE_CLIENT_SECRET'),
            ]);
        }

        if ($configId <= 0) {
            throw new GoogleException('Não foi possível persistir a configuração da integração.');
        }

        $email = $this->resolveAccountEmail($client);
        $this->repository->saveTokens($configId, $refreshToken, $email);

        return [
            'id' => $configId,
            'email' => $email,
            'refresh_token' => $refreshToken,
        ];
    }

    public function accessToken(): ?string
    {
        try {
            $client = $this->client();

            if (!$client->isAccessTokenExpired()) {
                return (string) $client->getAccessToken()['access_token'];
            }

            $refreshToken = (string) $client->getRefreshToken();
            if ($refreshToken === '') {
                $this->markDisconnected();
                return null;
            }

            $token = $client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (isset($token['error'])) {
                $this->markDisconnected();
                return null;
            }

            return (string) $token['access_token'];
        } catch (Throwable $e) {
            $this->handleAuthFailure($e);
            return null;
        }
    }

    public function validateConnection(): bool
    {
        return $this->accessToken() !== null;
    }

    public function accountEmail(): ?string
    {
        $client = $this->client();

        try {
            $oauth2 = new Oauth2($client);
            $userinfo = $oauth2->userinfo->get();
            return (string) $userinfo->getEmail();
        } catch (Throwable) {
            return null;
        }
    }

    public function disconnect(): void
    {
        $config = $this->repository->findActive();
        $configId = (int) ($config['id'] ?? 0);
        if ($configId > 0) {
            $this->repository->setDesconectado($configId);
        }
    }

    private function resolveAccountEmail(Client $client): string
    {
        try {
            $oauth2 = new Oauth2($client);
            $userinfo = $oauth2->userinfo->get();
            return (string) $userinfo->getEmail();
        } catch (Throwable) {
            return '';
        }
    }

    private function markDisconnected(): void
    {
        $config = $this->repository->findActive();
        $configId = (int) ($config['id'] ?? 0);
        if ($configId > 0) {
            $this->repository->setDesconectado($configId);
        }
    }

    private function handleAuthFailure(Throwable $e): void
    {
        $this->markDisconnected();
        error_log('[STORAGE] Falha de autenticação Google: ' . $e->getMessage());
    }
}
