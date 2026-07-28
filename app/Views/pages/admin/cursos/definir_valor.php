<?php
$curso = $curso ?? [];
$pagamentos = $pagamentos ?? [];
$idCurso = (int) ($curso['id'] ?? 0);
?>
<section class="container py-4">
  <div class="bg-white border rounded-3 p-4 shadow-sm">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
      <h4 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pagamento — <?= htmlspecialchars((string) ($curso['nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></h4>
      <a class="btn btn-outline-secondary btn-sm" href="/admin/cursos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
    </div>

    <h5 class="mb-3">Formas de pagamento cadastradas</h5>

    <?php if (!empty($pagamentos)): ?>
      <div class="table-responsive mb-4">
        <table class="table table-sm table-striped align-middle">
          <thead>
            <tr>
              <th>Descrição</th>
              <th>Tipo</th>
              <th>Parcelas</th>
              <th>Valor</th>
              <th>Ativo</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pagamentos as $p): ?>
              <tr>
                <td><?= htmlspecialchars((string) ($p['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars((string) ($p['tipo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= (int) ($p['parcelas'] ?? 1) ?>x</td>
                <td>R$ <?= number_format((float) ($p['valor'] ?? 0), 2, ',', '.') ?></td>
                <td>
                  <span class="badge <?= (int) ($p['ativo'] ?? 1) == 1 ? 'bg-success' : 'bg-secondary' ?>">
                    <?= (int) ($p['ativo'] ?? 1) == 1 ? 'Sim' : 'Não' ?>
                  </span>
                </td>
                <td>
                  <button class="btn btn-sm btn-outline-primary editar-pagamento" data-id="<?= (int) ($p['id'] ?? 0) ?>" data-descricao="<?= htmlspecialchars((string) ($p['descricao'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-tipo="<?= htmlspecialchars((string) ($p['tipo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-parcelas="<?= (int) ($p['parcelas'] ?? 1) ?>" data-valor="<?= number_format((float) ($p['valor'] ?? 0), 2, '.', '') ?>" data-ativo="<?= htmlspecialchars((string) ($p['ativo'] ?? '1'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-pencil"></i></button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="text-muted small mb-4">Nenhuma forma de pagamento cadastrada.</div>
    <?php endif; ?>

    <hr>
    <h5 class="mb-3" id="formTitulo"><i class="bi bi-plus-circle me-1"></i>Nova forma de pagamento</h5>
    <form method="post" action="/admin/cursos/salvar-pagamento">
      <input type="hidden" name="id_curso" value="<?= $idCurso ?>">
      <input type="hidden" name="id" id="inputId" value="0">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" id="inputDescricao" class="form-control" maxlength="100" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Tipo</label>
          <select name="tipo" id="inputTipo" class="form-select">
            <option value="">Selecione...</option>
            <option value="PIX">PIX</option>
            <option value="BOLETO">Boleto</option>
            <option value="CARTAO">Cartão</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Parcelas</label>
          <input type="number" name="parcelas" id="inputParcelas" class="form-control" min="1" value="1">
        </div>
        <div class="col-md-2">
          <label class="form-label">Valor</label>
          <input type="text" name="valor" id="inputValor" class="form-control" placeholder="0,00" inputmode="decimal">
        </div>
        <div class="col-md-2">
          <label class="form-label">Ativo</label>
          <select name="ativo" id="inputAtivo" class="form-select">
            <option value="1">Sim</option>
            <option value="0">Não</option>
          </select>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Salvar</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="btnCancelarEdicao"><i class="bi bi-x-lg me-1"></i>Cancelar edição</button>
      </div>
    </form>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var btnCancelar = document.getElementById('btnCancelarEdicao');

  document.querySelectorAll('.editar-pagamento').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.getElementById('inputId').value = this.dataset.id;
      document.getElementById('inputDescricao').value = this.dataset.descricao;
      document.getElementById('inputTipo').value = this.dataset.tipo || '';
      document.getElementById('inputParcelas').value = this.dataset.parcelas;
      document.getElementById('inputValor').value = this.dataset.valor;
      document.getElementById('inputAtivo').value = this.dataset.ativo;
      document.getElementById('formTitulo').textContent = 'Editar forma de pagamento';
      btnCancelar.classList.remove('d-none');
    });
  });

  btnCancelar.addEventListener('click', function() {
    document.getElementById('inputId').value = '0';
    document.getElementById('inputDescricao').value = '';
    document.getElementById('inputTipo').value = '';
    document.getElementById('inputParcelas').value = '1';
    document.getElementById('inputValor').value = '';
    document.getElementById('inputAtivo').value = '1';
    document.getElementById('formTitulo').textContent = 'Nova forma de pagamento';
    btnCancelar.classList.add('d-none');
  });
});
</script>
