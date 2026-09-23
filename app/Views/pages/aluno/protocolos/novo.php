<?php
  $matriculasView = is_array($matriculas ?? null) ? $matriculas : [];
  $unicaMatricula = count($matriculasView) === 1 ? $matriculasView[0] : null;
?>

<section class="py-4" style="margin-top: 20px;">
  <div class="container">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; box-shadow: var(--card-shadow);">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Novo Protocolo</h4>
        <a class="btn btn-outline-secondary btn-sm" href="/aluno/protocolos"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
      </div>

      <p class="text-muted small">
        Descreva sua solicitação para a Secretaria. Você poderá acompanhar a resposta e continuar a conversa nesta mesma página.
      </p>

      <form method="post" action="/aluno/protocolos/criar" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Matrícula</label>
          <?php if (empty($matriculasView)): ?>
            <div class="alert alert-warning py-2 px-3 mb-0 small">Nenhuma matrícula encontrada para o seu usuário.</div>
            <input type="hidden" name="id_matricula" value="">
          <?php else: ?>
            <select class="form-select" name="id_matricula" id="selectMatricula">
              <option value="">Sem matrícula específica</option>
              <?php foreach ($matriculasView as $mt): ?>
                <option value="<?= (int) ($mt['matricula_id'] ?? 0) ?>"
                        data-curso="<?= htmlspecialchars((string) ($mt['curso_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                        <?= $unicaMatricula && (int) ($unicaMatricula['matricula_id'] ?? 0) === (int) ($mt['matricula_id'] ?? 0) ? 'selected' : '' ?>>
                  #<?= (int) ($mt['matricula_id'] ?? 0) ?> — <?= htmlspecialchars((string) ($mt['curso_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text" id="cursoSelecionado"></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label">Assunto <span class="text-danger">*</span></label>
          <select class="form-select" name="assunto" required>
            <option value="">Selecione o assunto</option>
            <option value="Solicitação de Documentos">Solicitação de Documentos</option>
            <option value="Assunto acadêmico">Assunto acadêmico</option>
            <option value="Emissão de Diploma/Certificado">Emissão de Diploma/Certificado</option>
            <option value="Questão Financeira">Questão Financeira</option>
            <option value="Evento">Evento</option>
            <option value="Solicitação Especial">Solicitação Especial</option>
            <option value="Outros assuntos">Outros assuntos</option>
          </select>
        </div>

        <div class="col-12">
          <label class="form-label">Mensagem <span class="text-danger">*</span></label>
          <textarea class="form-control" name="mensagem" rows="7" required placeholder="Descreva detalhadamente sua solicitação..."></textarea>
        </div>

        <div class="col-12">
          <button class="btn btn-success" type="submit"><i class="bi bi-send me-1"></i>Abrir protocolo</button>
        </div>
      </form>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var select = document.getElementById('selectMatricula');
  var destino = document.getElementById('cursoSelecionado');
  if (!select || !destino) return;

  function atualizar() {
    var opt = select.options[select.selectedIndex];
    var curso = opt ? opt.getAttribute('data-curso') : '';
    destino.textContent = curso ? 'Curso: ' + curso : '';
  }

  select.addEventListener('change', atualizar);
  atualizar();
});
</script>