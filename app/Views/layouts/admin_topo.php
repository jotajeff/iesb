<!doctype html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars((($title ?? '') !== '' ? $title . ' :: IESB' : 'Painel Admin :: IESB'), ENT_QUOTES, 'UTF-8') ?></title>
  <script>document.documentElement.classList.add('admin-js');</script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="/assets/css/app.css" rel="stylesheet" />
</head>
<body class="d-flex flex-column min-vh-100" style="background: #f4f6f9;">
<div class="admin-page-loader" id="adminPageLoader" role="status" aria-live="polite" aria-label="Carregando página">
  <div class="admin-page-loader__panel">
    <span class="admin-page-loader__spinner" aria-hidden="true"></span>
    <span class="admin-page-loader__label">Carregando painel</span>
    <span class="admin-page-loader__progress" aria-hidden="true"><span></span></span>
  </div>
</div>
