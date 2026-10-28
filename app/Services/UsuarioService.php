<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

final class UsuarioService
{
    public function __construct(
        private readonly UsuarioRepository $repository = new UsuarioRepository(),
    ) {
    }

    public function usuarios(int $limit = 200): array
    {
        return $this->repository->list($limit);
    }

    public function usuariosPorTipo(string $tipo, int $limit = 200): array
    {
        return $this->repository->listByTipo($tipo, $limit);
    }

    public function usuariosPorTipoPaginados(string $tipo, int $limit, int $offset): array
    {
        return $this->repository->listByTipoPaginated($tipo, $limit, $offset);
    }

    public function findUsuario(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function criarUsuario(string $nome, string $email, string $senha, string $tipo = 'aluno', string $ativo = '1', string $telefone = '', string $titulacao = ''): int
    {
        $idFuncao = $this->repository->findFunctionIdByName($tipo);
        $id = $this->repository->create([
            'nome' => trim($nome),
            'email' => trim($email),
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'tipo' => $tipo,
            'id_funcao' => $idFuncao,
            'telefone' => $telefone ?: null,
            'titulacao' => trim($titulacao) !== '' ? trim($titulacao) : null,
            'ativo' => $ativo,
        ]);

        if ($id > 0 && $idFuncao > 0) {
            $this->repository->linkFunction($id, $idFuncao);
        }

        return $id;
    }

    public function atualizarUsuario(int $id, string $senha = '', string $ativo = '1', string $nome = '', string $email = '', string $tipo = '', string $telefone = '', string $titulacao = ''): void
    {
        $payload = [];
        if ($nome !== '') {
            $payload['nome'] = $nome;
        }
        if ($email !== '') {
            $payload['email'] = $email;
        }
        if ($senha !== '') {
            $payload['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        }
        if ($tipo !== '') {
            $payload['tipo'] = $tipo;
        }
        if ($telefone !== '') {
            $payload['telefone'] = $telefone;
        }
        if ($titulacao !== '') {
            $payload['titulacao'] = $titulacao;
        }
        $payload['ativo'] = $ativo;

        $this->repository->update($id, $payload);
    }
}
