<!doctype html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <title><?= htmlspecialchars($title ?? 'IESB', ENT_QUOTES, 'UTF-8') ?></title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="theme-color" content="#efc02b" />
  <meta name="description" content="<?= htmlspecialchars($ogDescription ?? 'Referência nacional em formação profissional | Cursos livres, pós-graduação, ensino prático e foco no mercado de trabalho - Polo Faculdade De São Marcos', ENT_QUOTES, 'UTF-8') ?>" />

  <meta property="og:title" content="<?= htmlspecialchars($ogTitle ?? $title ?? 'IESB', ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:description" content="<?= htmlspecialchars($ogDescription ?? 'Referência nacional em formação profissional | Cursos livres, pós-graduação, ensino prático e foco no mercado de trabalho - Polo Faculdade De São Marcos', ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? (getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com') . '/assets/img/logo-main.png', ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:url" content="<?= htmlspecialchars($ogUrl ?? (getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com') . ($_SERVER['REQUEST_URI'] ?? '/'), ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:type" content="<?= htmlspecialchars($ogType ?? 'website', ENT_QUOTES, 'UTF-8') ?>" />
  <meta property="og:site_name" content="IESB - Inteligência Educacional Souza Brazil" />
  <meta property="og:locale" content="pt_BR" />

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "IESB - Inteligência Educacional Souza Brazil",
    "url": "<?= htmlspecialchars(getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com', ENT_QUOTES, 'UTF-8') ?>",
    "logo": "<?= htmlspecialchars((getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com') . '/assets/img/logo-main.png', ENT_QUOTES, 'UTF-8') ?>",
    "description": "Referência nacional em formação profissional | Cursos livres, pós-graduação, ensino prático e foco no mercado de trabalho - Polo Faculdade De São Marcos",
    "address": { "@type": "PostalAddress", "addressCountry": "BR" }
  }
  </script>

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "IESB - Inteligência Educacional Souza Brazil",
    "url": "<?= htmlspecialchars(getenv('APP_URL') ?: 'https://inteligenciaeducacionalsouzabrazil.com', ENT_QUOTES, 'UTF-8') ?>"
  }
  </script>

  <?php if (isset($schema) && is_array($schema)): ?>
    <?php foreach ($schema as $s): ?>
      <script type="application/ld+json">
      <?= json_encode($s, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) ?>
      </script>
    <?php endforeach; ?>
  <?php endif; ?>

  <link rel="icon" href="/assets/img/favicon.ico" type="image/x-icon" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet" />
  <link href="/assets/css/app.css" rel="stylesheet" />
</head>

<body>