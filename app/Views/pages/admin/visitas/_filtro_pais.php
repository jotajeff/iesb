<?php
$paisAtual = (string) ($pais ?? 'br');
$currentPaisUrl = (string) ($paisUrl ?? '/admin/visitas');
$formMethod = (string) ($paisFormMethod ?? 'get');
$paisHidden = is_array($paisHidden ?? null) ? $paisHidden : [];
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-3 p-2 rounded-3" style="background:#f8f9fa;border:1px solid #dee2e6;">
  <span class="text-muted small"><i class="bi bi-globe-americas me-1"></i>Origem:</span>
  <form method="<?= $formMethod === 'post' ? 'post' : 'get' ?>" action="<?= htmlspecialchars($currentPaisUrl, ENT_QUOTES, 'UTF-8') ?>" class="d-inline-flex align-items-center gap-3 mb-0">
    <?php foreach ($paisHidden as $hName => $hValue): ?>
      <input type="hidden" name="<?= htmlspecialchars((string) $hName, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars((string) $hValue, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <div class="form-check form-check-inline mb-0">
      <input class="form-check-input" type="checkbox" name="pais" value="br" id="paisBr" <?= $paisAtual === 'br' ? 'checked' : '' ?>>
      <label class="form-check-label" for="paisBr">Brasil</label>
    </div>
    <div class="form-check form-check-inline mb-0">
      <input class="form-check-input" type="checkbox" name="pais" value="todos" id="paisTodos" <?= $paisAtual === 'todos' ? 'checked' : '' ?>>
      <label class="form-check-label" for="paisTodos">Todos</label>
    </div>
  </form>
</div>
<script>
(function() {
  var br = document.getElementById('paisBr');
  var todos = document.getElementById('paisTodos');
  if (!br || !todos) return;
  br.addEventListener('change', function() { todos.checked = false; br.form.submit(); });
  todos.addEventListener('change', function() { br.checked = false; todos.form.submit(); });
})();
</script>
