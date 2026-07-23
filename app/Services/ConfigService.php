<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ConfigRepository;

final class ConfigService
{
    public function __construct(
        private readonly ConfigRepository $repository = new ConfigRepository(),
    ) {
    }

    public function modalidades(): array
    {
        return $this->repository->listModalidades();
    }

    public function findModalidade(int $id): ?array
    {
        return $this->repository->findModalidadeById($id);
    }

    public function saveModalidade(int $id, string $nome, int $ativo): int
    {
        return $this->repository->saveModalidade([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativo === 1 ? 1 : 0,
        ]);
    }

    public function segmentos(): array
    {
        return $this->repository->listSegmentos();
    }

    public function findSegmento(int $id): ?array
    {
        return $this->repository->findSegmentoById($id);
    }

    public function saveSegmento(int $id, string $nome, string $ativo): int
    {
        $ativoSanitizado = strtoupper(trim($ativo)) === 'N' ? 'N' : 'S';

        return $this->repository->saveSegmento([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativoSanitizado,
        ]);
    }

    public function niveis(): array
    {
        return $this->repository->listNiveis();
    }

    public function findNivel(int $id): ?array
    {
        return $this->repository->findNivelById($id);
    }

    public function findNivelBySlug(string $slug): ?array
    {
        return $this->repository->findNivelBySlug($slug);
    }

    public function saveNivel(int $id, string $nome, int $ativo, string $apresentacao): int
    {
        return $this->repository->saveNivel([
            'id' => $id,
            'nome' => trim($nome),
            'ativo' => $ativo === 1 ? 1 : 0,
            'apresentacao' => trim($apresentacao),
        ]);
    }

    public function categorias(): array
    {
        return $this->repository->listCategorias();
    }

    public function findCategoria(int $id): ?array
    {
        return $this->repository->findCategoriaById($id);
    }

    public function saveCategoria(int $id, string $nome, string $slug, int $ativo): int
    {
        if ($slug === '') {
            $slug = \App\Services\CursoService::slugify($nome);
        }

        return $this->repository->saveCategoria([
            'id' => $id,
            'nome' => trim($nome),
            'slug' => trim($slug),
            'ativo' => $ativo === 1 ? 1 : 0,
        ]);
    }

    public function funcoesDocente(): array
    {
        return $this->repository->listFuncoesDocente();
    }

    public function findFuncaoDocente(int $id): ?array
    {
        return $this->repository->findFuncaoDocenteById($id);
    }

    public function saveFuncaoDocente(int $id, string $nome, string $descricao, string $ativo): int
    {
        $ativoSanitizado = strtoupper(trim($ativo)) === 'N' ? 'N' : 'S';

        return $this->repository->saveFuncaoDocente([
            'id' => $id,
            'nome' => trim($nome),
            'descricao' => trim($descricao),
            'ativo' => $ativoSanitizado,
        ]);
    }

    public function instituicoes(): array
    {
        return $this->repository->listInstituicoes();
    }

    public function findInstituicao(int $id): ?array
    {
        return $this->repository->findInstituicaoById($id);
    }

    public function saveInstituicao(array $data): int
    {
        return $this->repository->saveInstituicao($data);
    }
}
