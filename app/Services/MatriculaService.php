<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class MatriculaService
{
    public function __construct(
        private readonly CursoParcelaService $parcelaService = new CursoParcelaService(),
        private readonly AlunoService $alunoService = new AlunoService(),
        private readonly TurmaService $turmaService = new TurmaService(),
        private readonly \App\Repositories\MatriculaRepository $matriculaRepository = new \App\Repositories\MatriculaRepository(),
        private readonly NotificacaoMatriculaService $notificacaoMatriculaService = new NotificacaoMatriculaService(),
        private readonly RecorrenciaService $recorrenciaService = new RecorrenciaService(),
    ) {
    }

    /**
     * @param array<string, mixed> $payment
     * @return array<string, mixed>
     */
    public function confirmarPagamento(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            throw new RuntimeException('Payment ID vazio');
        }

        // Cobrança recorrente: nunca executa inscrição/matrícula.
        if (trim((string) ($payment['subscription'] ?? '')) !== '') {
            return $this->processarCobrancaRecorrente($payment);
        }

        $inscricao = $this->parcelaService->findByAsaasPayment($paymentId);
        if (!$inscricao) {
            $inscricao = $this->recorrenciaService->vincularParcelaRecorrente($payment);
        }
        if (!$inscricao) {
            $inscricao = $this->vincularParcelaPorReferencia($payment);
        }
        if (!$inscricao) {
            throw new RuntimeException('Inscrição não encontrada');
        }

        $idInscricao = (int) ($inscricao['id'] ?? 0);
        if ($idInscricao <= 0) {
            throw new RuntimeException('Inscrição inválida');
        }

        // Parcelas 2..N já possuem aluno e matrícula vinculados na geração.
        // Basta confirmar o status, sem criar nova matrícula.
        $idAlunoVinculado = (int) ($inscricao['id_aluno'] ?? 0);
        $idMatriculaVinculada = (int) ($inscricao['id_matricula'] ?? 0);
        $numeroParcela = (int) ($inscricao['numero_parcela'] ?? 1);
        if ($numeroParcela > 1 && $idAlunoVinculado > 0 && $idMatriculaVinculada > 0) {
            $this->parcelaService->atualizarStatus($idInscricao, 'CONFIRMADO', $idAlunoVinculado, $idMatriculaVinculada);
            return [
                'message' => 'Parcela confirmada com sucesso',
                'inscricaoId' => $idInscricao,
                'alunoId' => $idAlunoVinculado,
                'matriculaId' => $idMatriculaVinculada,
            ];
        }

        $matriculaExistente = $this->matriculaRepository->findByPagamento($idInscricao);
        if ($matriculaExistente !== null) {
            $this->criarAssinaturaSeNecessario($inscricao);
            $this->garantirParcelasRestantesDireta($inscricao);
            $this->notificacaoMatriculaService->enviar(
                (int) ($matriculaExistente['id_aluno'] ?? $inscricao['id_aluno'] ?? 0),
                (int) ($matriculaExistente['id_curso'] ?? $inscricao['id_curso'] ?? 0),
                (string) ($inscricao['nome'] ?? ''),
                (string) ($inscricao['email'] ?? ''),
                (string) ($inscricao['cpf'] ?? ''),
                (int) ($matriculaExistente['id'] ?? 0)
            );
            return [
                'message' => 'Pagamento já processado anteriormente',
                'inscricaoId' => $idInscricao,
                'alunoId' => (int) ($inscricao['id_aluno'] ?? 0),
                'matriculaId' => (int) ($matriculaExistente['id'] ?? 0),
                'numeroMatricula' => (string) ($matriculaExistente['numero'] ?? ''),
            ];
        }

        $this->parcelaService->atualizarStatus($idInscricao, 'RECEBIDO');

        return $this->efetivarMatricula($inscricao);
    }

    /**
     * Conclui a matrícula de uma parcela paga (1ª parcela): cria o aluno se
     * necessário, seleciona a turma, cria a matrícula e dispara a recorrência.
     *
     * @param array<string, mixed> $inscricao
     * @return array<string, mixed>
     */
    private function efetivarMatricula(array $inscricao): array
    {
        $idInscricao = (int) ($inscricao['id'] ?? 0);

        try {
            $cpf = preg_replace('/\D/', '', (string) ($inscricao['cpf'] ?? ''));
            $nome = (string) ($inscricao['nome'] ?? '');
            $email = (string) ($inscricao['email'] ?? '');
            $telefone = (string) ($inscricao['telefone'] ?? '');
            $idCurso = (int) ($inscricao['id_curso'] ?? 0);

            if ($cpf === '' || $nome === '' || $idCurso <= 0) {
                throw new RuntimeException('Dados insuficientes para matrícula');
            }

            $aluno = $this->alunoService->findByCpf($cpf);
            if ($aluno) {
                $idAluno = (int) ($aluno['id'] ?? 0);
            } else {
                $dataNascimento = (string) ($inscricao['data_nascimento'] ?? '2000-01-01');
                if ($dataNascimento === '') {
                    $dataNascimento = '2000-01-01';
                }
                $idAluno = $this->alunoService->criarAluno($nome, $cpf, $dataNascimento, $telefone, $email);
                if ($idAluno <= 0) {
                    throw new RuntimeException('Falha ao criar aluno');
                }
            }

            $turmas = $this->turmaService->turmasDoCurso($idCurso);
            if ($turmas === []) {
                throw new RuntimeException('Nenhuma turma ativa encontrada para o curso');
            }

            $idTurmaInscricao = (int) ($inscricao['id_turma'] ?? 0);
            if ($idTurmaInscricao > 0) {
                $turma = $this->turmaService->findTurma($idTurmaInscricao);
                if ($turma === null || intval($turma['ativo'] ?? 0) !== 1) {
                    $turma = $this->selecionarTurma($turmas);
                }
            } else {
                $turma = $this->selecionarTurma($turmas);
            }

            $idTurma = (int) ($turma['id'] ?? 0);
            if ($idTurma <= 0) {
                throw new RuntimeException('Turma inválida');
            }

            $idMatricula = $this->criarMatricula($idAluno, $idCurso, $idTurma, $idInscricao);

            $this->parcelaService->atualizarStatus($idInscricao, 'CONFIRMADO', $idAluno, $idMatricula);

            $inscricao['id_aluno'] = $idAluno;
            $inscricao['id_matricula'] = $idMatricula;

            // Inscrição direta (sem acordo): gera as demais parcelas no dia 10 de cada mês.
            // assim que a 1ª é paga, para constarem no financeiro do aluno.
            $this->garantirParcelasRestantesDireta($inscricao);

            $this->criarAssinaturaSeNecessario($inscricao);

            $this->notificacaoMatriculaService->enviar(
                $idAluno,
                $idCurso,
                $nome,
                $email,
                $cpf,
                $idMatricula
            );

            return [
                'message' => 'Matrícula realizada com sucesso',
                'inscricaoId' => $idInscricao,
                'alunoId' => $idAluno,
                'matriculaId' => $idMatricula,
            ];
        } catch (\Throwable $e) {
            $this->parcelaService->atualizarStatus($idInscricao, 'CANCELADO');
            throw $e instanceof RuntimeException ? $e : new RuntimeException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Gera as parcelas restantes (2..N) de uma inscrição direta feita pelo
     * site (sem acordo de pagamento), com vencimento no dia 10 de cada mês.
     * Idempotente.
     * Não faz nada para acordos (o fluxo deles é tratado pela recorrência).
     *
     * @param array<string, mixed> $inscricao
     */
    private function garantirParcelasRestantesDireta(array $inscricao): void
    {
        if ((int) ($inscricao['id_acordo_pagamento'] ?? 0) > 0) {
            return;
        }

        if ((int) ($inscricao['total_parcelas'] ?? 1) <= 1) {
            return;
        }

        try {
            $this->parcelaService->gerarParcelasRestantesPorPlano($inscricao);
        } catch (\Throwable $e) {
            error_log('[INSCRICAO DIRETA] Erro ao gerar parcelas restantes: ' . $e->getMessage());
        }

        $this->criarAssinaturaInscricaoDireta($inscricao);
    }

    /**
     * Cria a assinatura recorrente de uma inscrição direta (sem acordo) após o
     * pagamento da 1ª parcela, quando o aluno autorizou recorrencia_cartao.
     * Idempotente. Nunca é executado para acordos (fluxo separado).
     *
     * @param array<string, mixed> $inscricao
     */
    private function criarAssinaturaInscricaoDireta(array $inscricao): void
    {
        if ((int) ($inscricao['id_acordo_pagamento'] ?? 0) > 0) {
            return;
        }

        if ((int) ($inscricao['numero_parcela'] ?? 1) !== 1) {
            return;
        }

        if ((int) ($inscricao['recorrencia_cartao'] ?? 0) !== 1) {
            return;
        }

        if (trim((string) ($inscricao['asaas_subscription'] ?? '')) !== '') {
            return;
        }

        if ((int) ($inscricao['total_parcelas'] ?? 1) <= 1) {
            return;
        }

        $idParcelaOrigem = (int) ($inscricao['id'] ?? 0);
        $customerId = (string) ($inscricao['asaas_customer'] ?? '');
        if ($idParcelaOrigem <= 0 || $customerId === '') {
            return;
        }

        try {
            $totalParcelas = (int) ($inscricao['total_parcelas'] ?? 1);
            $parcelas = $this->parcelaService->listarPorInscricao(
                (int) ($inscricao['id_aluno'] ?? 0),
                (int) ($inscricao['id_pagamento'] ?? 0),
                (int) ($inscricao['id_curso'] ?? 0)
            );

            $dataInicio = '';
            $dataFim = '';
            foreach ($parcelas as $parcela) {
                $numero = (int) ($parcela['numero_parcela'] ?? 0);
                if ($numero === 2 && $dataInicio === '') {
                    $dataInicio = (string) ($parcela['data_vencimento'] ?? '');
                }
                if ($numero === $totalParcelas && $dataFim === '') {
                    $dataFim = (string) ($parcela['data_vencimento'] ?? '');
                }
            }

            $dataBase = (string) ($inscricao['data_vencimento'] ?? date('Y-m-d'));
            if ($dataInicio === '') {
                $dataInicio = $this->calcularVencimentoMensal($dataBase, 1);
            }
            if ($dataFim === '') {
                $dataFim = $this->calcularVencimentoMensal($dataBase, $totalParcelas - 1);
            }

            $valor = (float) ($inscricao['valor'] ?? 0);
            $descricao = (string) ($inscricao['descricao_pagamento'] ?? 'Inscrição');

            $asaas = new \App\Services\AsaasService();
            $resultado = $asaas->criarAssinatura([
                'customer_id' => $customerId,
                'billing_type' => 'CREDIT_CARD',
                'value' => $valor,
                'cycle' => 'MONTHLY',
                'next_due_date' => $dataInicio,
                'end_date' => $dataFim,
                'description' => $descricao . ' - recorrência',
                'external_reference' => (string) $idParcelaOrigem,
            ]);

            if ($resultado === null) {
                $this->parcelaService->atualizarRecorrencia($idParcelaOrigem, [
                    'status_recorrencia' => 'ERRO',
                ]);
                error_log('[INSCRICAO DIRETA] Falha ao criar assinatura: ' . ($asaas->getLastError() ?? 'erro desconhecido'));
                return;
            }

            $this->parcelaService->atualizarRecorrencia($idParcelaOrigem, [
                'asaas_subscription' => (string) ($resultado['id'] ?? ''),
                'status_recorrencia' => 'ATIVA',
                'data_inicio_recorrencia' => $dataInicio,
                'data_fim_recorrencia' => $dataFim,
            ]);
        } catch (\Throwable $e) {
            error_log('[INSCRICAO DIRETA] Erro ao criar assinatura: ' . $e->getMessage());
        }
    }

    /**
     * Reprocessa uma parcela já paga (status RECEBIDO ou CONFIRMADO) para
     * concluir o fluxo: matrícula (se ainda não existir) e recorrência.
     * Idempotente.
     *
     * @param array<string, mixed> $inscricao
     * @return array<string, mixed>
     */
    public function reprocessarParcela(array $inscricao): array
    {
        $idInscricao = (int) ($inscricao['id'] ?? 0);
        if ($idInscricao <= 0) {
            return ['id' => 0, 'status' => 'erro', 'message' => 'Parcela inválida'];
        }

        if (!in_array((string) ($inscricao['status'] ?? ''), ['RECEBIDO', 'CONFIRMADO'], true)) {
            return ['id' => $idInscricao, 'status' => 'ignorada', 'message' => 'Parcela não está paga'];
        }

        $numeroParcela = (int) ($inscricao['numero_parcela'] ?? 1);
        $idAluno = (int) ($inscricao['id_aluno'] ?? 0);
        $idMatricula = (int) ($inscricao['id_matricula'] ?? 0);

        if ($numeroParcela > 1 && $idAluno > 0 && $idMatricula > 0) {
            $this->parcelaService->atualizarStatus($idInscricao, 'CONFIRMADO', $idAluno, $idMatricula);
            return ['id' => $idInscricao, 'status' => 'ok', 'message' => 'Parcela recorrente já confirmada'];
        }

        $matriculaExistente = $this->matriculaRepository->findByPagamento($idInscricao);
        if ($matriculaExistente !== null) {
            $this->criarAssinaturaSeNecessario($inscricao);
            $this->garantirParcelasRestantesDireta($inscricao);
            return [
                'id' => $idInscricao,
                'status' => 'ok',
                'message' => 'Matrícula já existente',
                'matriculaId' => (int) ($matriculaExistente['id'] ?? 0),
            ];
        }

        $resultado = $this->efetivarMatricula($inscricao);
        $resultado['id'] = $idInscricao;
        $resultado['status'] = 'ok';

        return $resultado;
    }

    /**
     * Varre as parcelas com status RECEBIDO/CONFIRMADO que ainda não possuem
     * matrícula vinculada e efetiva o fluxo. Usado como reconciliação quando o
     * webhook foi interrompido.
     *
     * @return array<int, array<string, mixed>>
     */
    public function reprocessarParcelasSemMatricula(): array
    {
        $parcelas = $this->parcelaService->listarPagasSemMatricula();
        $resultados = [];

        foreach ($parcelas as $parcela) {
            try {
                $resultados[] = $this->reprocessarParcela($parcela);
            } catch (\Throwable $e) {
                $resultados[] = [
                    'id' => (int) ($parcela['id'] ?? 0),
                    'status' => 'erro',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $resultados;
    }

    /**
     * Cria a assinatura recorrente após a confirmação da 1ª parcela do acordo,
     * desde que o aluno tenha autorizado e ainda não exista assinatura.
     *
     * @param array<string, mixed> $inscricao
     */
    private function calcularVencimentoMensal(string $dataBase, int $meses): string
    {
        try {
            $base = new \DateTimeImmutable($dataBase);
        } catch (\Throwable) {
            $base = new \DateTimeImmutable('today');
        }

        $mes = $base->modify('first day of +' . max(0, $meses) . ' month');
        return $mes->setDate(
            (int) $mes->format('Y'),
            (int) $mes->format('m'),
            10
        )->format('Y-m-d');
    }

    private function criarAssinaturaSeNecessario(array $inscricao): void
    {
        if ((int) ($inscricao['numero_parcela'] ?? 1) !== 1) {
            return;
        }

        $idAcordo = (int) ($inscricao['id_acordo_pagamento'] ?? 0);
        if ($idAcordo <= 0) {
            return;
        }

        try {
            $this->recorrenciaService->criarAssinatura($inscricao);
        } catch (\Throwable $e) {
            error_log('[RECORRENCIA] Erro ao criar assinatura: ' . $e->getMessage());
        }
    }

    /**
     * Tenta vincular o pagamento a uma parcela cujo id foi gravado como
     * externalReference na cobrança (ex.: primeira cobrança do acordo) e que
     * ainda não possui asaas_payment. Protege o fluxo contra a perda do
     * asaas_payment na parcela.
     *
     * @param array<string, mixed> $payment
     * @return array<string, mixed>|null
     */
    private function vincularParcelaPorReferencia(array $payment): ?array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        $externalReference = (string) ($payment['externalReference'] ?? '');
        if ($paymentId === '' || $externalReference === '' || !ctype_digit($externalReference)) {
            return null;
        }

        $parcela = $this->parcelaService->findByExternalReference((int) $externalReference);
        if ($parcela === null) {
            return null;
        }

        $this->parcelaService->atualizarAsaasInfo((int) $parcela['id'], [
            'asaas_payment' => $paymentId,
        ]);

        return $this->parcelaService->buscar((int) $parcela['id']);
    }

    /**
     * Cria a matrícula na tabela "matricula" com número próprio.
     */
    private function criarMatricula(int $idAluno, int $idCurso, int $idTurma, int $idPagamento): int
    {
        $ano = (int) date('Y');
        $numero = (string) $this->matriculaRepository->proximoNumero($ano);

        $idMatricula = $this->matriculaRepository->create([
            'numero' => $numero,
            'id_aluno' => $idAluno,
            'id_curso' => $idCurso,
            'id_turma' => $idTurma,
            'id_pagamento' => $idPagamento,
            'origem' => 'SITE',
            'status' => 'ATIVA',
            'data_matricula' => date('Y-m-d H:i:s'),
            'ativo' => 1,
        ]);

        if ($idMatricula <= 0) {
            throw new RuntimeException('Falha ao criar matrícula');
        }

        return $idMatricula;
    }

    /**
     * @param array<string, mixed> $payment
     * @return array<string, mixed>
     */
    public function atualizarPagamento(array $payment): array
    {
        $paymentId = (string) ($payment['id'] ?? '');
        if ($paymentId === '') {
            throw new RuntimeException('Payment ID vazio');
        }

        // Cobrança recorrente: nunca executa inscrição/matrícula.
        if (trim((string) ($payment['subscription'] ?? '')) !== '') {
            return $this->processarCobrancaRecorrente($payment);
        }

        $inscricao = $this->parcelaService->findByAsaasPayment($paymentId);
        if (!$inscricao) {
            $inscricao = $this->recorrenciaService->vincularParcelaRecorrente($payment);
        }
        if (!$inscricao) {
            $inscricao = $this->vincularParcelaPorReferencia($payment);
        }
        if (!$inscricao) {
            throw new RuntimeException('Inscrição não encontrada');
        }

        $newStatus = (string) ($payment['status'] ?? '');
        $novoStatus = $this->mapearStatus($newStatus);

        if ($novoStatus !== null) {
            $this->parcelaService->atualizarStatus((int) ($inscricao['id'] ?? 0), $novoStatus);

            // Pagamento recebido/confirmado: garantir que a matricula foi criada.
            if (in_array($novoStatus, ['RECEBIDO', 'CONFIRMADO'], true)) {
                return $this->confirmarPagamento($payment);
            }

            return [
                'message' => 'Status atualizado',
                'status' => $novoStatus,
                'inscricaoId' => (int) ($inscricao['id'] ?? 0),
            ];
        }

        return [
            'message' => 'Status não mapeado',
            'status' => $newStatus,
            'inscricaoId' => (int) ($inscricao['id'] ?? 0),
        ];
    }

    /**
     * Processa uma cobrança recorrente (payment.subscription != NULL).
     *
     * Associa o asaas_payment à parcela existente do acordo e atualiza o
     * status conforme o evento. NUNCA cria inscrição, aluno ou matrícula.
     *
     * @param array<string, mixed> $payment
     * @return array<string, mixed>
     */
    public function processarCobrancaRecorrente(array $payment): array
    {
        $parcela = $this->recorrenciaService->vincularParcelaRecorrente($payment);
        if ($parcela === null) {
            throw new RuntimeException('Cobrança recorrente sem parcela correspondente');
        }

        $idParcela = (int) ($parcela['id'] ?? 0);

        $novoStatus = $this->mapearStatus((string) ($payment['status'] ?? ''));
        if ($novoStatus !== null && $idParcela > 0) {
            $this->parcelaService->atualizarStatus($idParcela, $novoStatus);
        }

        return [
            'message' => 'Cobrança recorrente processada',
            'inscricaoId' => $idParcela,
            'alunoId' => (int) ($parcela['id_aluno'] ?? 0),
            'matriculaId' => (int) ($parcela['id_matricula'] ?? 0),
            'status' => $novoStatus ?? (string) ($parcela['status'] ?? ''),
        ];
    }

    /**
     * Converte o status do Asaas para a convenção do sistema.
     */
    private function mapearStatus(string $status): ?string
    {
        $map = [
            'PENDING' => 'PENDENTE',
            'RECEIVED' => 'RECEBIDO',
            'CONFIRMED' => 'CONFIRMADO',
            'OVERDUE' => 'CANCELADO',
            'REFUNDED' => 'ESTORNADO',
            'CANCELED' => 'CANCELADO',
            'RECEIVED_IN_CASH' => 'RECEBIDO',
        ];

        return $map[$status] ?? null;
    }

    /**
     * @param array<int, array<string, mixed>> $turmas
     * @return array<string, mixed>
     */
    private function selecionarTurma(array $turmas): array
    {
        foreach ($turmas as $turma) {
            if (intval($turma['ativo'] ?? 0) === 1) {
                return $turma;
            }
        }

        return $turmas[0];
    }
}
