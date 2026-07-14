<?php
// Incluso da tutte le pagine admin_*. Richiede $admin già caricato e $activeAdminTab impostato.
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Admin') ?> — myband.it</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="navbar">
  <div class="brand"><a href="/">myband<span>.it</span></a> <small style="color:var(--text-muted)">/ admin</small></div>
  <nav>
    <a href="/dashboard_profile.php">La mia pagina</a>
    <a href="/logout.php">Esci</a>
  </nav>
</div>
<div class="container">
  <div class="tabs">
    <a href="/admin_dashboard.php" class="<?= $activeAdminTab==='dashboard'?'active':'' ?>">Dashboard</a>
    <a href="/admin_users.php" class="<?= $activeAdminTab==='users'?'active':'' ?>">Utenti iscritti</a>
    <a href="/admin_contacts.php" class="<?= $activeAdminTab==='contacts'?'active':'' ?>">Contatti ricevuti</a>
    <a href="/admin_privacy.php" class="<?= $activeAdminTab==='privacy'?'active':'' ?>">Privacy / Cookie</a>
    <a href="/admin_tracking.php" class="<?= $activeAdminTab==='tracking'?'active':'' ?>">Tracking (GTM/Pixel)</a>
    <a href="/admin_smtp.php" class="<?= $activeAdminTab==='smtp'?'active':'' ?>">Email / SMTP</a>
  </div>
