<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TarefaRepository;

final class TarefaService
{
    public function __construct(
        private readonly TarefaRepository $repository = new TarefaRepository(),
    ) {
    }

    public function tarefas(int $limit = 300): array
    {
        return $this->repository->list($limit);
    }

    public function findTarefa(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function criarTarefa(int $setorId, string $tarefa, int $criadoPor, ?int $responsavelId, string $situacao, int $prioridade = 1): int
    {
        return $this->repository->create([
            'setor' => $setorId,
            'tarefa' => trim($tarefa),
            'criado_por' => $criadoPor,
            'responsavel' => $responsavelId ?? 0,
            'situacao' => $situacao,
            'prioridade' => $prioridade,
        ]);
    }

    public function atualizarTarefa(int $id, int $setorId, string $tarefa, ?int $responsavelId, string $situacao, int $prioridade = 1): void
    {
        $this->repository->update($id, [
            'setor' => $setorId,
            'tarefa' => trim($tarefa),
            'responsavel' => $responsavelId ?? 0,
            'situacao' => $situacao,
            'prioridade' => $prioridade,
        ]);
    }

    public function setores(): array
    {
        return $this->repository->listSetores();
    }

    public function findSetor(int $id): ?array
    {
        return $this->repository->findSetorById($id);
    }

    public function saveSetor(int $id, string $setor): int
    {
        return $this->repository->saveSetor([
            'id' => $id,
            'setor' => trim($setor),
        ]);
    }
}
