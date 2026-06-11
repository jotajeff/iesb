<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Vincular Professor a Turmas</h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/professores"><i class="bi bi-arrow-left me-1"></i>Voltar para lista</a>
    </div>

    <p class="mb-3"><strong>Professor:</strong> <?= htmlspecialchars((string) ($professor['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>

    <form method="post" action="/admin/professores/salvar-vinculo">
      <input type="hidden" name="id" value="<?= (int) ($professor['id'] ?? 0) ?>">

      <?php $turmasLista = is_array($turmas ?? null) ? $turmas : []; ?>
      <?php $vinculosMap = is_array($vinculos ?? null) ? $vinculos : []; ?>

      <?php if (empty($turmasLista)): ?>
        <div class="alert alert-light border text-muted">Nenhuma turma cadastrada.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-sm align-middle">
            <thead>
              <tr>
                <th style="width:60px"><i class="bi bi-check-square me-1"></i>Vincular</th>
                <th>Turma</th>
                <th>Curso</th>
                <th>Data Início</th>
                <th>Ativa</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($turmasLista as $t): ?>
                <?php $idTurma = (int) ($t['id'] ?? 0); ?>
                <?php if ($idTurma <= 0) continue; ?>
                <tr>
                  <td class="text-center">
                    <input class="form-check-input" type="checkbox" name="turmas[]" value="<?= $idTurma ?>"
                      id="turma_<?= $idTurma ?>"
                      <?= isset($vinculosMap[$idTurma]) ? 'checked' : '' ?>>
                  </td>
                  <td><label class="form-check-label" for="turma_<?= $idTurma ?>"><?= htmlspecialchars((string) ($t['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></label></td>
                  <td><?= htmlspecialchars((string) ($t['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($t['data_inicio'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <?php $ativa = (string) ($t['ativa'] ?? 'N'); ?>
                    <span class="badge <?= $ativa === 'S' ? 'bg-success' : 'bg-secondary' ?>">
                      <?= $ativa === 'S' ? 'Sim' : 'Não' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <div class="mt-3">
        <button class="btn btn-success" type="submit"><i class="bi bi-check-lg me-1"></i>Salvar Vínculo</button>
      </div>
    </form>
  </div>
</section>
