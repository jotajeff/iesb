<?php
$dashboardUser = $authUser ?? [];
$dashboardName = (string) ($dashboardUser['name'] ?? 'Administrador');
$dashboardType = (string) ($dashboardUser['role'] ?? $dashboardUser['type'] ?? 'admin');
$taskIndicators = $taskIndicators ?? [
  'tarefas_criadas' => 0,
  'tarefas_execucao' => 0,
  'tarefas_finalizadas' => 0,
];
$isAdmin = (bool) ($isAdmin ?? ($dashboardType === 'admin'));
$canViewCourseEnrollmentCards = in_array($dashboardType, ['admin', 'operador'], true);

$dashboardTypeLabel = match ($dashboardType) {
  'admin' => 'Administrador',
  'operador' => 'Operador',
  'aluno' => 'Aluno',
  default => ucfirst($dashboardType),
};
?>
<section class="container py-4">
  <div class="bg-white border rounded-4 p-4 shadow-sm mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="d-flex align-items-center gap-3">
        <a href="<?= htmlspecialchars((string) ($fotosUrl ?? '/admin/usuarios'), ENT_QUOTES, 'UTF-8') ?>"
           class="text-decoration-none d-inline-flex align-items-center justify-content-center rounded-circle overflow-hidden border border-2 border-light-subtle shadow-sm bg-light flex-shrink-0"
           style="width:64px;height:64px;"
           title="Foto do perfil">
          <?php if (!empty($userFoto)): ?>
            <img src="/<?= htmlspecialchars((string) $userFoto, ENT_QUOTES, 'UTF-8') ?>" alt="Foto do usuário" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <i class="bi bi-camera" style="font-size:1.6rem;color:#6c757d;"></i>
          <?php endif; ?>
        </a>
        <div>
          <div class="text-uppercase text-muted small fw-semibold mb-1">Bem-vindo ao painel</div>
          <h4 class="mb-1">Olá, <?= htmlspecialchars($dashboardName, ENT_QUOTES, 'UTF-8') ?></h4>
          <p class="mb-0 text-muted">Seu nível de acesso atual é <strong><?= htmlspecialchars($dashboardTypeLabel, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
        </div>
      </div>
      <div class="text-end">
        <span class="badge bg-dark text-uppercase"><?= htmlspecialchars($dashboardTypeLabel, ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php if ($dashboardType !== 'professor'): ?>
    <div class="col-md-3">
      <a href="/admin/preinscricao" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-preinscricao h-100<?= (int) ($indicators['total_pre_inscricoes'] ?? 0) > 0 ? ' task-card-alert' : '' ?>">
          <div class="task-card-top">
            <div>
              <small class="task-card-label">Pré-inscrições</small>
              <h2 class="task-card-value mb-0"><?= (int) ($indicators['total_pre_inscricoes'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-preinscricao">
              <i class="bi bi-inbox-fill"></i>
            </div>
          </div>
          <div class="task-card-footer">Aguardando contato</div>
        </div>
      </a>
    </div>
    <?php endif; ?>
    <?php if ($canViewCourseEnrollmentCards): ?>
    <div class="col-md-4">
      <a href="/admin/matriculas" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-matricula h-100">
          <div class="task-card-top">
            <div>
              <small class="task-card-label">Matriculados</small>
              <h2 class="task-card-value mb-0"><?= (int) ($indicators['total_matricula'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-matricula">
              <i class="bi bi-person-check"></i>
            </div>
          </div>
          <div class="task-card-footer">Matrículas ativas</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="/admin/cursos" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-cursos h-100">
          <div class="task-card-top">
            <div>
              <small class="task-card-label">Cursos</small>
              <h2 class="task-card-value mb-0"><?= (int) ($indicators['total_cursos'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-cursos">
              <i class="bi bi-journal-bookmark-fill"></i>
            </div>
          </div>
          <div class="task-card-footer">Cursos cadastrados</div>
        </div>
      </a>
    </div>
    <?php endif; ?>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <a href="/admin/tarefas" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-tarefa h-100">
          <div class="task-card-top">
            <div>
              <small class="task-card-label"><?= $isAdmin ? 'Tarefas criadas' : 'Minhas tarefas criadas' ?></small>
              <h2 class="task-card-value mb-0"><?= (int) ($taskIndicators['tarefas_criadas'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-tarefa">
              <i class="bi bi-kanban"></i>
            </div>
          </div>
          <div class="task-card-footer">Etapa inicial do fluxo</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="/admin/tarefas/lista?situacao=execucao" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-execucao h-100">
          <div class="task-card-top">
            <div>
              <small class="task-card-label"><?= $isAdmin ? 'Tarefas em execução' : 'Minhas tarefas em execução' ?></small>
              <h2 class="task-card-value mb-0"><?= (int) ($taskIndicators['tarefas_execucao'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-execucao">
              <i class="bi bi-kanban"></i>
            </div>
          </div>
          <div class="task-card-footer">Itens em andamento ou revisão</div>
        </div>
      </a>
    </div>
    <div class="col-md-4">
      <a href="/admin/tarefas/lista?situacao=finalizada" class="text-decoration-none d-block h-100">
        <div class="dashboard-task-card task-card-finalizado h-100">
          <div class="task-card-top">
            <div>
              <small class="task-card-label"><?= $isAdmin ? 'Tarefas finalizadas' : 'Minhas tarefas finalizadas' ?></small>
              <h2 class="task-card-value mb-0"><?= (int) ($taskIndicators['tarefas_finalizadas'] ?? 0) ?></h2>
            </div>
            <div class="task-card-icon task-card-icon-finalizado">
              <i class="bi bi-kanban"></i>
            </div>
          </div>
          <div class="task-card-footer">Concluídas e prontas</div>
        </div>
      </a>
    </div>
  </div>
</section>

<style>
.dashboard-task-card {
  position: relative;
  overflow: hidden;
  border-radius: 1.25rem;
  padding: 1.15rem;
  background: #fff;
  border: 1px solid rgba(77, 79, 78, 0.1);
  box-shadow: 0 10px 26px rgba(77, 79, 78, 0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.dashboard-task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 34px rgba(77, 79, 78, 0.14);
}

.dashboard-task-card::before {
  content: "";
  position: absolute;
  inset: 0 auto 0 0;
  width: 8px;
  border-radius: 1.25rem 0 0 1.25rem;
}

.task-card-tarefa::before {
  background: var(--warning, #ffc107);
}

.task-card-execucao::before {
  background: var(--primary, #0d6efd);
}

.task-card-finalizado::before {
  background: var(--success, #198754);
}

.task-card-alunos::before {
  background: var(--info, #0dcaf0);
}

.task-card-matricula::before {
  background: #fd7e14;
}

.task-card-cursos::before {
  background: #6f42c1;
}

.task-card-preinscricao::before {
  background: var(--pink, #d63384);
}

.task-card-alert {
  animation: taskCardAlertPulse 2.8s ease-in-out infinite;
}

@keyframes taskCardAlertPulse {
  0% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.5);
  }
  70% {
    box-shadow: 0 0 0 18px rgba(220, 53, 69, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
  }
}

.task-card-alert:hover {
  animation: taskCardAlertPulse 2.8s ease-in-out infinite;
}

.task-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  padding-left: 0.45rem;
}

.task-card-label {
  display: inline-block;
  margin-bottom: 0.35rem;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  font-size: 0.72rem;
  font-weight: 700;
}

.task-card-value {
  line-height: 1;
  font-size: 2.7rem;
  font-weight: 800;
  color: var(--text-heading);
}

.task-card-icon {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.5rem;
  color: #fff;
}

.task-card-icon-tarefa {
  background: linear-gradient(135deg, #f59f00, #ffd43b);
}

.task-card-icon-execucao {
  background: linear-gradient(135deg, #0d6efd, #66b2ff);
}

.task-card-icon-finalizado {
  background: linear-gradient(135deg, #198754, #5dd39e);
}

.task-card-icon-alunos {
  background: linear-gradient(135deg, #0dcaf0, #6edff7);
}

.task-card-icon-cursos {
  background: linear-gradient(135deg, #6f42c1, #b197fc);
}

.task-card-icon-matricula {
  background: linear-gradient(135deg, #fd7e14, #ffb067);
}

.task-card-icon-preinscricao {
  background: linear-gradient(135deg, #d63384, #e599c4);
}

.task-card-footer {
  margin-top: 0.95rem;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(77, 79, 78, 0.1);
  color: var(--text-secondary);
  font-size: 0.88rem;
  padding-left: 0.45rem;
}

@media (max-width: 767.98px) {
  .task-card-value {
    font-size: 2.2rem;
  }

  .task-card-icon {
    width: 48px;
    height: 48px;
    font-size: 1.2rem;
  }
}
</style>
