<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PermissaoRepository;

final class PermissaoService
{
    public function __construct(private readonly PermissaoRepository $repository = new PermissaoRepository())
    {
    }

    public function modulos(): array
    {
        return $this->repository->listarModulos();
    }

    public function modulo(int $id): ?array
    {
        return $this->repository->buscarModulo($id);
    }

    public function salvarModulo(array $dados): int
    {
        return $this->repository->salvarModulo($dados);
    }

    public function funcoes(): array
    {
        return $this->repository->listarFuncoes();
    }

    public function funcao(int $id): ?array
    {
        return $this->repository->buscarFuncao($id);
    }

    public function salvarFuncao(array $dados): int
    {
        return $this->repository->salvarFuncao($dados);
    }

    public function permissoesDaFuncao(int $idFuncao): array
    {
        return $this->repository->permissoesDaFuncao($idFuncao);
    }

    public function salvarPermissoes(int $idFuncao, array $permissoes): bool
    {
        return $this->repository->salvarPermissoes($idFuncao, $permissoes);
    }
}
