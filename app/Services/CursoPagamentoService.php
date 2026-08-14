<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CursoPagamentoRepository;

final class CursoPagamentoService
{
    public function __construct(
        private readonly CursoPagamentoRepository $repository = new CursoPagamentoRepository(),
    ) {
    }

    public function listarPorCurso(int $idCurso): array
    {
        return $this->repository->listarPorCurso($idCurso);
    }

    public function find(int $id): ?array
    {
        return $this->repository->findById($id);
    }

    public function salvar(array $data): int
    {
        return $this->repository->save($data);
    }

    public function deletar(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * Indica se o desconto promocional do plano está vigente.
     *
     * Desconto ativo quando percentual > 0, data limite preenchida
     * e data atual ainda dentro do prazo (data atual <= data limite).
     *
     * @param array<string, mixed> $pagamento
     */
    public function descontoVigente(array $pagamento): bool
    {
        $percentual = (float) ($pagamento['desconto_percentual'] ?? 0);
        $dataLimite = trim((string) ($pagamento['desconto_data_limite'] ?? ''));

        if ($percentual <= 0 || $dataLimite === '') {
            return false;
        }

        return date('Y-m-d') <= $dataLimite;
    }

    /**
     * Valor absoluto do desconto vigente (0.00 quando não aplicável).
     *
     * @param array<string, mixed> $pagamento
     */
    public function valorDesconto(array $pagamento): float
    {
        if (!$this->descontoVigente($pagamento)) {
            return 0.0;
        }

        $valor = (float) ($pagamento['valor'] ?? 0);
        $percentual = (float) ($pagamento['desconto_percentual'] ?? 0);

        return round($valor * ($percentual / 100), 2);
    }

    /**
     * Valor efetivo (promocional) do plano no momento.
     *
     * Retorna o valor original quando o desconto não está vigente.
     *
     * @param array<string, mixed> $pagamento
     */
    public function calcularValorEfetivo(array $pagamento): float
    {
        $valor = (float) ($pagamento['valor'] ?? 0);

        return round($valor - $this->valorDesconto($pagamento), 2);
    }

    /**
     * Enriquece o plano com os campos de desconto calculados para a view.
     *
     * @param array<string, mixed> $pagamento
     * @return array<string, mixed>
     */
    public function enriquecerComDesconto(array $pagamento): array
    {
        $original = (float) ($pagamento['valor'] ?? 0);
        $vigente = $this->descontoVigente($pagamento);
        $final = $vigente ? $this->calcularValorEfetivo($pagamento) : $original;

        $pagamento['desconto_vigente'] = $vigente;
        $pagamento['valor_original'] = $original;
        $pagamento['valor_final'] = $final;
        $pagamento['valor_desconto'] = round($original - $final, 2);

        return $pagamento;
    }

    /**
     * Lista os planos do curso com os campos de desconto calculados.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarPorCursoComDesconto(int $idCurso): array
    {
        $planos = $this->listarPorCurso($idCurso);

        return array_map(fn (array $pagamento): array => $this->enriquecerComDesconto($pagamento), $planos);
    }
}
