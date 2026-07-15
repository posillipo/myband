<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = currentUser();
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>MyBand.it — Il tuo Linktree da musicista</title>
<link rel="stylesheet" href="/assets/css/style.css">
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body>
<?= embedTrackingBodyStart() ?>
<div class="navbar">
  <div class="brand">myband<span>.it</span></div>
  <nav>
    <?php if ($user): ?>
      <a href="/dashboard.php">Dashboard</a>
      <a href="/logout.php">Esci</a>
    <?php else: ?>
      <a href="/login.php">Accedi</a>
      <a href="/register.php">Registrati</a>
    <?php endif; ?>
  </nav>
</div>

<div class="hero">
  <h1>Una pagina, tutta la tua musica</h1>
  <p>Crea in pochi minuti la tua pagina artista: link a Spotify e social, brani in ascolto, prossimi concerti, blog e contatti booking.</p>
  <a class="btn" href="/register.php">Crea la tua pagina gratis</a>
</div>

<div class="container">
  <div class="card">
    <strong>🎧 Player audio integrato</strong>
    <p style="color:var(--text-muted)">Carica i tuoi brani e falli ascoltare direttamente dalla tua pagina.</p>
  </div>
  <div class="card">
    <strong>🔗 Tutti i tuoi link in un posto</strong>
    <p style="color:var(--text-muted)">Spotify, Apple Music, YouTube, Instagram, TikTok e altro.</p>
  </div>
  <div class="card">
    <strong>📅 Calendario concerti</strong>
    <p style="color:var(--text-muted)">Mostra le prossime date con link ai biglietti.</p>
  </div>
  <div class="card">
    <strong>📬 Contatti e booking</strong>
    <p style="color:var(--text-muted)">Ricevi richieste direttamente dai fan e dagli organizzatori.</p>
  </div>
</div>

<footer class="site">myband.it &middot; piattaforma per musicisti</footer>
</body>
</html>
