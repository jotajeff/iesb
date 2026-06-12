<!doctype html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title ?? 'Área do Aluno', ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="/assets/css/app.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">

  <nav class="navbar navbar-expand-lg site-navbar">
    <div class="container">
      <a class="navbar-brand" href="/aluno">
        <img src="/assets/img/logo-top.png" alt="IESB Logo" class="logo w-50">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#alunoNavbar">
        <span class="navbar-toggler-icon"><span></span></span>
      </button>
      <div class="collapse navbar-collapse" id="alunoNavbar">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item">
            <a class="nav-link <?= ($currentRoute ?? '') === '/area-do-aluno' ? 'active' : '' ?>" href="/aluno">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($currentRoute ?? '') === '/aluno/cursos' ? 'active' : '' ?>" href="/aluno/cursos">
              <i class="bi bi-book me-1"></i>Meus Cursos
              <?php if (!empty($cursosMatriculados ?? [])): ?>
                <span class="badge bg-warning text-dark ms-1"><?= count($cursosMatriculados ?? []) ?></span>
              <?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($currentRoute ?? '') === '/aluno/logs' ? 'active' : '' ?>" href="/aluno/logs">
              <i class="bi bi-clock-history me-1"></i>Logs
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= ($currentRoute ?? '') === '/aluno/perfil' ? 'active' : '' ?>" href="/aluno/perfil">
              <i class="bi bi-person me-1"></i>Perfil
            </a>
          </li>
        </ul>
        <div class="d-flex align-items-center gap-2">
          <button class="theme-toggle" id="themeToggle" aria-label="Alternar tema">
            <div class="toggle-ball">
              <i class="bi bi-sun-fill toggle-icon" id="toggleIcon"></i>
            </div>
          </button>

          <form method="post" action="/logout" class="d-inline">
            <button type="submit" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-box-arrow-right me-1"></i>Sair
            </button>
          </form>
        </div>
      </div>
    </div>
  </nav>

  <main class="flex-grow-1 w-100">