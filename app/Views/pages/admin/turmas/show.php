<?php
  $turmaSelecionada = is_array($turma ?? null) ? $turma : null;
  $inscritosLista = is_array($inscritos ?? null) ? $inscritos : [];
  $totalInscritos = count($inscritosLista);
?>

<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-eye me-2"></i><?= htmlspecialchars((string) ($turmaSelecionada['nome'] ?? 'Turma'), ENT_QUOTES, 'UTF-8') ?></h4>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="/admin/turmas"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
        <a class="btn btn-outline-primary btn-sm" href="/admin/turmas/editar?id=<?= (int) ($turmaSelecionada['id'] ?? 0) ?>"><i class="bi bi-pencil-square me-1"></i>Editar</a>
      </div>
    </div>

    <?php if (!$turmaSelecionada): ?>
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>Turma não encontrada.
      </div>
    <?php else: ?>
      <table class="table table-bordered">
        <tbody>
          <tr>
            <th class="bg-light" style="width: 180px;">ID</th>
            <td><?= (int) ($turmaSelecionada['id'] ?? 0) ?></td>
          </tr>
          <tr>
            <th class="bg-light">Nome</th>
            <td><?= htmlspecialchars((string) ($turmaSelecionada['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th class="bg-light">Curso</th>
            <td><?= htmlspecialchars((string) ($turmaSelecionada['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th class="bg-light">Nível</th>
            <td><?= htmlspecialchars((string) ($turmaSelecionada['nivel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          </tr>
          <tr>
            <th class="bg-light">Data de Início</th>
            <td><?php
              $rawDate = (string) ($turmaSelecionada['data_inicio'] ?? '');
              $dt = $rawDate !== '' ? \DateTime::createFromFormat('Y-m-d', $rawDate) : false;
              echo htmlspecialchars($dt ? $dt->format('d/m/Y') : ($rawDate ?: '-'), ENT_QUOTES, 'UTF-8');
            ?></td>
          </tr>
          <tr>
            <th class="bg-light">Ativa</th>
            <td>
              <?php if (strtoupper(trim((string) ($turmaSelecionada['ativa'] ?? 'N'))) === 'S'): ?>
                <span class="badge bg-primary">Sim</span>
              <?php else: ?>
                <span class="badge bg-secondary">Não</span>
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th class="bg-light">Total Inscritos</th>
            <td><span class="badge bg-info"><?= $totalInscritos ?></span></td>
          </tr>
        </tbody>
      </table>

      <hr>
      <h5 class="mb-3"><i class="bi bi-people me-2"></i>Alunos inscritos (<?= $totalInscritos ?>)</h5>

      <?php if (empty($inscritosLista)): ?>
        <div class="alert alert-light border text-muted">
          <i class="bi bi-inbox me-1"></i>Nenhum aluno matriculado nesta turma.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-sm align-middle">
            <thead>
              <tr>
                <th><i class="bi bi-hash"></i></th>
                <th>Nome</th>
                <th>CPF</th>
                <th>Email</th>
                <th>Telefone</th>
                <th>Status</th>
                <th>Data Matrícula</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($inscritosLista as $aluno): ?>
                <tr>
                  <td><?= (int) ($aluno['id'] ?? 0) ?></td>
                  <td><?= htmlspecialchars((string) ($aluno['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($aluno['cpf'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($aluno['email'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($aluno['telefone'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php
                      $statusClass = match ($aluno['status'] ?? '') {
                        'ativo' => 'bg-success',
                        'concluido' => 'bg-primary',
                        'cancelado' => 'bg-danger',
                        'inadimplente' => 'bg-warning text-dark',
                        'inscrito' => 'bg-info',
                        default => 'bg-secondary',
                      };
                    ?>
                    <span class="badge <?= $statusClass ?>"><?= htmlspecialchars((string) ($aluno['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                  </td>
                  <td><?php
                    $raw = (string) ($aluno['data_matricula'] ?? '');
                    $dtm = $raw !== '' ? \DateTime::createFromFormat('Y-m-d H:i:s', $raw) ?: \DateTime::createFromFormat('Y-m-d', $raw) : false;
                    echo htmlspecialchars($dtm ? $dtm->format('d/m/Y H:i') : ($raw ?: '-'), ENT_QUOTES, 'UTF-8');
                  ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
