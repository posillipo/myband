<?php
// Incluso da tutte le pagine dashboard_*. Richiede $user già caricato e $activeTab impostato.
$dashTheme = ($user['dashboard_theme'] ?? 'dark') === 'light' ? 'light-theme' : '';
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Dashboard') ?> — myband.it</title>
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
</head>
<body class="<?= e($dashTheme) ?>">
<div class="navbar">
  <div class="brand"><a href="/">myband<span>.it</span></a></div>
  <nav>
    <a href="/<?= e($user['slug']) ?>" target="_blank">Vedi pagina pubblica ↗</a>
    <?php if (!empty($user['is_admin'])): ?>
      <a href="/admin_dashboard.php">Area Admin</a>
    <?php endif; ?>
    <a href="/logout.php">Esci</a>
  </nav>
</div>
<div class="container">
  <div class="tabs">
    <a href="/dashboard_profile.php" class="<?= $activeTab==='profile'?'active':'' ?>">Profilo</a>
    <a href="/dashboard_links.php" class="<?= $activeTab==='links'?'active':'' ?>">Link</a>
    <a href="/dashboard_audio.php" class="<?= $activeTab==='audio'?'active':'' ?>">Brani</a>
    <a href="/dashboard_events.php" class="<?= $activeTab==='events'?'active':'' ?>">Concerti</a>
    <a href="/dashboard_blog.php" class="<?= $activeTab==='blog'?'active':'' ?>">Blog</a>
    <a href="/dashboard_contacts.php" class="<?= $activeTab==='contacts'?'active':'' ?>">Contatti</a>
  </div>
