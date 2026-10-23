<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FuncaoUsuarioRepository;

final class FuncaoUsuarioService
{
    public function __construct(
        private readonly FuncaoUsuarioRepository $repository = new FuncaoUsuarioRepository(),
    ) {
    }

    public function usuariosComFuncoes(): array
    {
        return $this->repository->listarUsuariosComFuncoes();
    }

    public function usuario(int $id): ?array
    {
        return $this->repository->buscarUsuario($id);
    }

    public function funcoes(): array
    {
        return $this->repository->listarFuncoes();
    }

    public function funcoesDoUsuario(int $idUsuario): array
    {
        return $this->repository->funcoesDoUsuario($idUsuario);
    }

    public function salvarFuncoesDoUsuario(int $idUsuario, array $idsFuncoes): bool
    {
        return $this->repository->salvarFuncoesDoUsuario($idUsuario, $idsFuncoes);
    }
}
