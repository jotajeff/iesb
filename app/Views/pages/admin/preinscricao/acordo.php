<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
      <div>
        <h4 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Acordos</h4>
        <p class="text-muted mb-0 small">Lista dos pré-inscritos que possuem acordo de pagamento negociado pela secretaria.</p>
      </div>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/preinscricao"><i class="bi bi-arrow-left me-1"></i>Voltar às pré-inscrições</a>
    </div>

    <?php if (!empty($flash ?? '')): ?>
      <div class="alert alert-info"><?= htmlspecialchars((string) $flash, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php
      $reprocessados = $reprocessados ?? [];
      $efetivados = array_values(array_filter(
          $reprocessados,
          static fn (array $r): bool => ($r['status'] ?? '') === 'ok'
      ));
      $erros = array_values(array_filter(
          $reprocessados,
          static fn (array $r): bool => ($r['status'] ?? '') === 'erro'
      ));
    ?>
    <?php if ($efetivados !== []): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-1"></i><strong><?= count($efetivados) ?> pagamento(s)</strong> processado(s): matrícula e/ou recorrência efetivadas.
      </div>
    <?php endif; ?>
    <?php if ($erros !== []): ?>
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>Erros ao processar pagamentos:
        <?php foreach ($erros as $erro): ?>
          <div class="small">Parcela #<?= (int) ($erro['id'] ?? 0) ?>: <?= htmlspecialchars((string) ($erro['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-striped table-sm align-middle">
        <thead>
          <tr>
            <th>#</th>
            <th>Pré-inscrito</th>
            <th>Curso</th>
            <th>Plano</th>
            <th>Tipo</th>
            <th>Entrada</th>
            <th>Parcelas</th>
            <th>Valor demais</th>
            <th>Status</th>
            <th>Criado em</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($acordos ?? [])): ?>
            <tr><td colspan="10" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum acordo encontrado.</td></tr>
          <?php else: ?>
            <?php
              $tiposAcordo = [1 => 'Padrão', 2 => 'À vista', 3 => 'Entrada + parcelas'];
            ?>
            <?php foreach ($acordos as $acordo): ?>
              <?php
                $acordoTipo = (int) ($acordo['tipo'] ?? 1);
                $acordoUtilizado = (int) ($acordo['utilizado'] ?? 0) === 1;
                $acordoAtivo = (int) ($acordo['ativo'] ?? 0) === 1;
                $acordoPago = (int) ($acordo['pago'] ?? 0) === 1;
                $ultimoStatus = (string) ($acordo['ultimo_status'] ?? '');
                $criadoEm = (string) ($acordo['created_at'] ?? '');
                $dt = $criadoEm !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $criadoEm) : false;
              ?>
              <tr>
                <td>#<?= (int) ($acordo['id'] ?? 0) ?></td>
                <td>
                  <div class="fw-semibold"><?= htmlspecialchars((string) ($acordo['pre_inscrito_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
                  <?php if (!empty($acordo['pre_inscrito_email'])): ?>
                    <div class="text-muted small"><?= htmlspecialchars((string) $acordo['pre_inscrito_email'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars((string) ($acordo['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) ($acordo['plano_descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($tiposAcordo[$acordoTipo] ?? 'Tipo ' . $acordoTipo, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td>R$ <?= number_format((float) ($acordo['valor_entrada'] ?? 0), 2, ',', '.') ?></td>
                <td><?= (int) ($acordo['total_parcelas'] ?? 1) ?>x</td>
                <td>R$ <?= number_format((float) ($acordo['valor_demais_parcelas'] ?? 0), 2, ',', '.') ?></td>
                <td>
                  <?php if (!$acordoAtivo): ?>
                    <span class="badge bg-secondary">Inativo</span>
                  <?php elseif ($acordoPago): ?>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Pago</span>
                  <?php elseif ($ultimoStatus !== ''): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pagamento <?= htmlspecialchars(strtolower((string) $ultimoStatus), ENT_QUOTES, 'UTF-8') ?></span>
                  <?php elseif ($acordoUtilizado): ?>
                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pagamento pendente</span>
                  <?php else: ?>
                    <span class="badge bg-info text-dark"><i class="bi bi-hourglass me-1"></i>Não utilizado</span>
                  <?php endif; ?>
                </td>
                <td><?= $dt ? $dt->format('d/m/Y H:i') : ($criadoEm ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class="mt-2 text-muted small">
        Total de acordos: <strong><?= count($acordos ?? []) ?></strong>
      </div>
    </div>
  </div>
</section>
