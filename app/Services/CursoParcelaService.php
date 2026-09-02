<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CursoParcelaRepository;

final class CursoParcelaService
{
    public function __construct(
        private readonly CursoParcelaRepository $repository = new CursoParcelaRepository(),
    ) {
    }

    public function criar(array $data): int
    {
        return $this->repository->create($data);
    }

    public function criarComAcordo(array $data): int
    {
        return $this->repository->createComAcordo($data);
    }

    public function buscar(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function listarPorAluno(int $idAluno): array
    {
        return $this->repository->listByAluno($idAluno);
    }

    public function listarPorAcordo(int $idAcordo): array
    {
        return $this->repository->listByAcordo($idAcordo);
    }

    public function listarPorInscricao(int $idAluno, int $idPagamento, int $idCurso): array
    {
        return $this->repository->listByInscricao($idAluno, $idPagamento, $idCurso);
    }

    /**
     * Gera as parcelas restantes (2..N) para inscrições feitas pelo site,
     * com intervalo de 30 dias. Usa o total_parcelas do plano (cursos_pagamento)
     * já gravado na parcela origem.
     *
     * @param array<string, mixed> $parcelaOrigem
     */
    public function gerarParcelasRestantesPorPlano(array $parcelaOrigem): int
    {
        $totalParcelas = (int) ($parcelaOrigem['total_parcelas'] ?? 1);
        if ($totalParcelas <= 1) {
            return 0;
        }

        $idAluno = (int) ($parcelaOrigem['id_aluno'] ?? 0);
        $idPagamento = (int) ($parcelaOrigem['id_pagamento'] ?? 0);
        $idCurso = (int) ($parcelaOrigem['id_curso'] ?? 0);
        if ($idAluno <= 0 || $idPagamento <= 0 || $idCurso <= 0) {
            return 0;
        }

        $existentes = $this->listarPorInscricao($idAluno, $idPagamento, $idCurso);
        $numerosExistentes = array_map(
            static fn (array $p): int => (int) ($p['numero_parcela'] ?? 0),
            $existentes
        );

        $dataBase = (string) ($parcelaOrigem['data_vencimento'] ?? date('Y-m-d'));
        $valorParcela = (float) ($parcelaOrigem['valor'] ?? 0);
        $idMatricula = (int) ($parcelaOrigem['id_matricula'] ?? 0);
        $idTurma = isset($parcelaOrigem['id_turma']) ? (int) $parcelaOrigem['id_turma'] : 0;
        $descricao = (string) ($parcelaOrigem['descricao_pagamento'] ?? '');

        $criadas = 0;
        for ($n = 2; $n <= $totalParcelas; $n++) {
            if (in_array($n, $numerosExistentes, true)) {
                continue;
            }

            $dataVenc = (new \DateTimeImmutable($dataBase))->modify('+' . (($n - 1) * 30) . ' days')->format('Y-m-d');

            $id = $this->criar([
                'id_curso' => $idCurso,
                'id_pagamento' => $idPagamento,
                'id_turma' => $idTurma,
                'numero_parcela' => $n,
                'total_parcelas' => $totalParcelas,
                'descricao_pagamento' => $descricao,
                'nome' => (string) ($parcelaOrigem['nome'] ?? ''),
                'cpf' => (string) ($parcelaOrigem['cpf'] ?? ''),
                'email' => (string) ($parcelaOrigem['email'] ?? ''),
                'telefone' => (string) ($parcelaOrigem['telefone'] ?? ''),
                'valor' => $valorParcela,
                'data_vencimento' => $dataVenc,
            ]);

            if ($id > 0) {
                $this->atualizarStatus($id, 'PENDENTE', $idAluno, $idMatricula);
                $criadas++;
            }
        }

        return $criadas;
    }

    public function gerarParcelasRestantes(array $parcelaOrigem, array $acordo): int
    {
        $totalParcelas = (int) ($parcelaOrigem['total_parcelas'] ?? $acordo['total_parcelas'] ?? 1);
        if ($totalParcelas <= 1) {
            return 0;
        }

        $idAcordo = (int) ($parcelaOrigem['id_acordo_pagamento'] ?? 0);
        if ($idAcordo <= 0) {
            return 0;
        }

        $existentes = $this->listarPorAcordo($idAcordo);
        $numerosExistentes = array_map(
            static fn (array $p): int => (int) ($p['numero_parcela'] ?? 0),
            $existentes
        );

        $dataBase = (string) ($parcelaOrigem['data_vencimento'] ?? date('Y-m-d'));
        $valorDemais = (float) ($acordo['valor_demais_parcelas'] ?? $parcelaOrigem['valor'] ?? 0);
        $idCurso = (int) ($parcelaOrigem['id_curso'] ?? 0);
        $idPagamento = (int) ($parcelaOrigem['id_pagamento'] ?? 0);
        $idTurma = isset($parcelaOrigem['id_turma']) ? (int) $parcelaOrigem['id_turma'] : 0;
        $idPreInscricao = (int) ($parcelaOrigem['id_pre_inscricao'] ?? 0);
        $idAluno = (int) ($parcelaOrigem['id_aluno'] ?? 0);
        $idMatricula = (int) ($parcelaOrigem['id_matricula'] ?? 0);
        $descricao = (string) ($parcelaOrigem['descricao_pagamento'] ?? '');

        $criadas = 0;
        for ($n = 2; $n <= $totalParcelas; $n++) {
            if (in_array($n, $numerosExistentes, true)) {
                continue;
            }

            $dataVenc = date('Y-m-d', strtotime($dataBase . ' + ' . (($n - 1) * 30) . ' days'));

            $id = $this->criarComAcordo([
                'id_curso' => $idCurso,
                'id_pagamento' => $idPagamento,
                'id_turma' => $idTurma,
                'id_pre_inscricao' => $idPreInscricao,
                'id_acordo_pagamento' => $idAcordo,
                'numero_parcela' => $n,
                'total_parcelas' => $totalParcelas,
                'descricao_pagamento' => $descricao,
                'nome' => (string) ($parcelaOrigem['nome'] ?? ''),
                'cpf' => (string) ($parcelaOrigem['cpf'] ?? ''),
                'email' => (string) ($parcelaOrigem['email'] ?? ''),
                'telefone' => (string) ($parcelaOrigem['telefone'] ?? ''),
                'valor' => $valorDemais,
                'data_vencimento' => $dataVenc,
            ]);

            if ($id > 0) {
                $this->atualizarStatus($id, 'PENDENTE', $idAluno, $idMatricula);
                $criadas++;
            }
        }

        return $criadas;
    }

    public function atualizarAsaasInfo(int $id, array $data): bool
    {
        return $this->repository->updateAsaasInfo($id, $data);
    }

    public function vincularAcordo(int $id, int $idAcordo): bool
    {
        return $this->repository->vincularAcordo($id, $idAcordo);
    }

    public function findByAsaasPayment(string $asaasPayment): ?array
    {
        return $this->repository->findByAsaasPayment($asaasPayment);
    }

    public function findByExternalReference(int $id): ?array
    {
        return $this->repository->findByExternalReference($id);
    }

    public function findByAsaasSubscription(string $subscription): ?array
    {
        return $this->repository->findByAsaasSubscription($subscription);
    }

    public function atualizarRecorrencia(int $id, array $data): bool
    {
        return $this->repository->atualizarRecorrencia($id, $data);
    }

    public function listarPagasSemMatricula(): array
    {
        return $this->repository->listarPagasSemMatricula();
    }

    public function atualizarStatus(int $id, string $status, ?int $idAluno = null, ?int $idMatricula = null): bool
    {
        return $this->repository->updateStatus($id, $status, $idAluno, $idMatricula);
    }

    public function vincularAlunoPorAcordo(int $idAcordo, int $idAluno, int $idMatricula): bool
    {
        return $this->repository->vincularAlunoPorAcordo($idAcordo, $idAluno, $idMatricula);
    }
}
