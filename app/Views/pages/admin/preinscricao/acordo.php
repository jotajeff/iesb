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
      $enviosPorAcordo = $enviosPorAcordo ?? [];
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
            <th>Parcelas / Valor demais</th>
            <th>Status</th>
            <th>E-mail</th>
            <th>Criado em</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($acordos ?? [])): ?>
            <tr><td colspan="10" class="text-muted"><i class="bi bi-inbox me-1"></i>Nenhum acordo encontrado.</td></tr>
          <?php else: ?>
            <?php
              $tiposAcordo = [1 => 'Padrão', 2 => 'À vista', 3 => 'Entrada + parcelas', 4 => 'Somente parcelas'];
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
                <td><a href="/admin/preinscricao/detalhe?id=<?= (int) ($acordo['id_pre_inscricao'] ?? 0) ?>" class="text-decoration-none">#<?= (int) ($acordo['id'] ?? 0) ?></a></td>
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
                <td><?= (int) ($acordo['total_parcelas'] ?? 1) ?>x — R$ <?= number_format((float) ($acordo['valor_demais_parcelas'] ?? 0), 2, ',', '.') ?></td>
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
                <?php
                  $acordoId = (int) ($acordo['id'] ?? 0);
                  $enviosAcordo = $enviosPorAcordo[$acordoId] ?? [];
                  $ultimoEnvio = $enviosAcordo[0] ?? null;
                  $ultimoStatusEnvio = $ultimoEnvio !== null ? (string) ($ultimoEnvio['status'] ?? '') : '';
                  $ultimoEnvioData = $ultimoEnvio !== null ? (string) ($ultimoEnvio['data_envio'] ?? '') : '';
                  $enviosJson = htmlspecialchars((string) json_encode($enviosAcordo, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                ?>
                <td>
                  <?php if ($ultimoEnvio === null): ?>
                    <span class="text-muted">—</span>
                  <?php else: ?>
                    <?php if ($ultimoStatusEnvio === 'ENVIADO'): ?>
                      <span class="text-success"><i class="bi bi-check-circle me-1"></i>Enviado</span>
                    <?php elseif ($ultimoStatusEnvio === 'ERRO'): ?>
                      <span class="text-danger"><i class="bi bi-x-circle me-1"></i>Erro</span>
                    <?php else: ?>
                      <span class="text-warning"><i class="bi bi-clock me-1"></i>Pendente</span>
                    <?php endif; ?>
                    <?php if ($ultimoEnvioData !== ''): ?>
                      <div class="text-muted small"><?= date('d/m/Y H:i', strtotime($ultimoEnvioData)) ?></div>
                    <?php endif; ?>
                    <?php if (count($enviosAcordo) > 1 || $ultimoStatusEnvio === 'ERRO'): ?>
                      <button type="button" class="btn btn-sm btn-link p-0 mt-1 btn-ver-historico"
                              data-acordo-id="<?= $acordoId ?>"
                              data-historico='<?= $enviosJson ?>'>
                        <i class="bi bi-clock-history me-1"></i>Ver histórico
                      </button>
                    <?php endif; ?>
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

<div class="modal fade" id="modalHistoricoEmail" tabindex="-1" aria-labelledby="modalHistoricoEmailLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalHistoricoEmailLabel"><i class="bi bi-envelope me-2"></i>Histórico de e-mails do acordo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr>
                <th>#</th>
                <th>Destinatário</th>
                <th>Data/Hora</th>
                <th>Status</th>
                <th>Erro</th>
              </tr>
            </thead>
            <tbody id="historicoEmailBody">
              <tr><td colspan="5" class="text-muted">Nenhum envio registrado.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalEl = document.getElementById('modalHistoricoEmail');
  var tbody = document.getElementById('historicoEmailBody');
  var modalTitle = document.getElementById('modalHistoricoEmailLabel');

  function formatDataHora(valor) {
    if (!valor) {
      return '-';
    }
    var d = new Date(valor.replace(' ', 'T'));
    if (isNaN(d.getTime())) {
      return valor;
    }
    return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
  }

  function esc(str) {
    return String(str == null ? '' : str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function badgeStatus(status) {
    if (status === 'ENVIADO') {
      return '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Enviado</span>';
    }
    if (status === 'ERRO') {
      return '<span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Erro</span>';
    }
    return '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pendente</span>';
  }

  document.querySelectorAll('.btn-ver-historico').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var historico = [];
      try {
        historico = JSON.parse(btn.getAttribute('data-historico')) || [];
      } catch (e) {
        historico = [];
      }

      if (modalTitle) {
        modalTitle.textContent = 'Histórico de e-mails do acordo #' + btn.getAttribute('data-acordo-id');
      }

      if (!historico.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Nenhum envio registrado.</td></tr>';
      } else {
        var rows = '';
        historico.forEach(function (envio) {
          var nome = envio.nome_destinatario || '';
          var email = envio.email_destinatario || '-';
          var destinatario = nome !== '' ? esc(nome) + ' &lt;' + esc(email) + '&gt;' : esc(email);
          var erro = envio.erro ? esc(envio.erro) : '-';
          rows += '<tr>'
            + '<td>#' + envio.id + '</td>'
            + '<td>' + destinatario + '</td>'
            + '<td>' + formatDataHora(envio.data_envio || envio.created_at) + '</td>'
            + '<td>' + badgeStatus(envio.status) + '</td>'
            + '<td class="small text-danger">' + erro + '</td>'
            + '</tr>';
        });
        tbody.innerHTML = rows;
      }

      if (modalEl) {
        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
      }
    });
  });
});
</script>
