<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\LogRepository;

final class LogService
{
    public function __construct(
        private readonly LogRepository $repository = new LogRepository(),
        private readonly IpLocationService $ipLocation = new IpLocationService(),
    ) {
    }

    public function logs(int $page = 1, int $perPage = 50, ?string $perfil = null, ?string $nome = null): array
    {
        $result = $this->repository->recent($page, $perPage, $perfil, $nome);
        $logs = $result['data'];
        $total = $result['total'];

        $resolved = [];
        foreach ($logs as $log) {
            $ip = (string) ($log['ip'] ?? '');
            if ($ip !== '' && $ip !== '-') {
                $log['location'] = $this->ipLocation->resolve($ip);
            } else {
                $log['location'] = [
                    'country' => '-',
                    'city' => '-',
                    'country_code' => '',
                    'flag' => "\u{1F3F3}\u{FE0F}",
                ];
            }
            $resolved[] = $log;
        }

        $totalPages = max(1, (int) ceil($total / $perPage));

        return [
            'data' => $resolved,
            'pagination' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => $totalPages,
            ],
        ];
    }

    public function log(
        string $acao,
        string $entidade,
        int $entidadeId,
        string $descricao,
        bool $sucesso = true
    ): void {
        $user = \App\Support\Session::get('user');
        $usuarioId = (int) ($user['id'] ?? 0);
        $perfil = (string) ($user['role'] ?? 'sistema');
        $perfisValidos = ['admin', 'aluno', 'professor', 'operador', 'sistema'];
        if (!in_array($perfil, $perfisValidos, true)) {
            $perfil = 'sistema';
        }

        $this->repository->registrar($usuarioId, $perfil, $acao, $entidade, $entidadeId, $descricao, $sucesso);
    }
}
