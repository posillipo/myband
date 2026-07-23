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
<title>myband.it — Il Linktree per musicisti indipendenti</title>
<meta name="description" content="Una pagina, tutta la tua musica: link, brani, eventi, blog e contatti booking in un unico posto.">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #FAF5EE; color: #17172b; }
  a { text-decoration: none; color: inherit; }

  .lp-nav {
    display: flex; align-items: center; justify-content: space-between;
    max-width: 1180px; margin: 0 auto; padding: 18px 24px;
  }
  .lp-nav .lp-logo { font-weight: 800; font-size: 20px; display: flex; align-items: center; gap: 8px; }
  .lp-nav .lp-logo .dot { width: 10px; height: 10px; border-radius: 50%; background: rgb(108,92,231); display: inline-block; }
  .lp-nav-links { display: flex; gap: 28px; font-weight: 600; font-size: 14.5px; color: #444; }
  .lp-nav-links a:hover { color: rgb(108,92,231); }
  .lp-nav-cta { background: #17172b; color: #fff; padding: 10px 22px; border-radius: 999px; font-weight: 700; font-size: 14px; }
  @media (max-width: 800px) { .lp-nav-links { display: none; } }

  .lp-hero {
    max-width: 1180px; margin: 40px auto 0; padding: 20px 24px 60px;
    display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 40px; align-items: center;
  }
  .lp-hero h1 { font-size: 56px; line-height: 1.08; font-weight: 800; margin: 0 0 22px; letter-spacing: -1px; }
  .lp-hero h1 .hl { color: rgb(108,92,231); }
  .lp-hero p.lp-sub { font-size: 17px; color: #55555f; max-width: 480px; line-height: 1.6; margin-bottom: 32px; }
  .lp-cta-row { display: flex; gap: 14px; flex-wrap: wrap; }
  .lp-btn-dark { background: #17172b; color: #fff; padding: 15px 30px; border-radius: 999px; font-weight: 700; font-size: 15px; }
  .lp-btn-outline { background: transparent; color: #17172b; padding: 14px 28px; border-radius: 999px; font-weight: 700; font-size: 15px; border: 1.5px solid #ccc; }
  @media (max-width: 900px) {
    .lp-hero { grid-template-columns: 1fr; text-align: center; }
    .lp-hero h1 { font-size: 38px; }
    .lp-hero p.lp-sub { margin-left: auto; margin-right: auto; }
    .lp-cta-row { justify-content: center; }
  }

  /* Illustrazione: mini mockup di una pagina myBand vera, con qualche carta fluttuante attorno */
  .lp-illustration { position: relative; display: flex; justify-content: center; min-height: 420px; }
  .lp-phone {
    width: 230px; background: linear-gradient(160deg, #FFD6A5 0%, #A0C4FF 55%, #BDB2FF 100%);
    border-radius: 32px; padding: 22px 16px; box-shadow: 0 30px 60px rgba(23,23,43,0.25);
    text-align: center; position: relative; z-index: 2;
  }
  .lp-phone .lp-avatar { width: 56px; height: 56px; border-radius: 50%; background: #fff; margin: 0 auto 10px; border: 3px solid #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.15); }
  .lp-phone .lp-name { font-weight: 800; font-size: 13px; margin-bottom: 2px; }
  .lp-phone .lp-handle { font-size: 10px; color: rgba(23,23,43,0.6); margin-bottom: 12px; }
  .lp-phone .lp-pill-row { display: flex; gap: 4px; justify-content: center; flex-wrap: wrap; margin-bottom: 14px; }
  .lp-phone .lp-pill-row span { background: rgba(255,255,255,0.6); border-radius: 7px; font-size: 8px; font-weight: 700; padding: 3px 6px; }
  .lp-phone .lp-link-btn { display: block; border-radius: 10px; padding: 10px; font-size: 11px; font-weight: 700; margin-bottom: 8px; color: #17172b; }
  .lp-float-card {
    position: absolute; background: #fff; border-radius: 14px; padding: 10px 14px;
    box-shadow: 0 10px 26px rgba(23,23,43,0.15); font-size: 12.5px; font-weight: 700; z-index: 3;
  }
  .lp-float-1 { top: 10px; right: 0; transform: rotate(4deg); }
  .lp-float-2 { top: 150px; right: -20px; transform: rotate(-3deg); }
  .lp-float-3 { bottom: 30px; right: 10px; transform: rotate(3deg); }
  @media (max-width: 900px) {
    .lp-float-card { display: none; }
  }

  .lp-features { max-width: 1180px; margin: 20px auto 80px; padding: 0 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; }
  .lp-feature { background: #fff; border-radius: 18px; padding: 24px; box-shadow: 0 4px 20px rgba(23,23,43,0.06); }
  .lp-feature .lp-feature-icon { font-size: 26px; margin-bottom: 12px; }
  .lp-feature h3 { font-size: 16px; margin: 0 0 8px; }
  .lp-feature p { font-size: 13.5px; color: #666; line-height: 1.5; margin: 0; }
  @media (max-width: 900px) { .lp-features { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 550px) { .lp-features { grid-template-columns: 1fr; } }

  .lp-final-cta { text-align: center; padding: 60px 24px 90px; }
  .lp-final-cta h2 { font-size: 32px; margin: 0 0 14px; }
  .lp-final-cta p { color: #666; margin-bottom: 28px; }

  .lp-footer { text-align: center; padding: 30px 24px; color: #999; font-size: 13px; border-top: 1px solid #eee; }
</style>
</head>
<body>
<?= embedTrackingBodyStart() ?>

<nav class="lp-nav">
  <div class="lp-logo"><span class="dot"></span> myBand.it</div>
  <div class="lp-nav-links">
    <a href="#come-funziona">Come funziona</a>
    <a href="#funzionalita">Funzionalità</a>
    <a href="/request_access.php">Richiedi accesso</a>
  </div>
  <?php if ($user): ?>
    <a href="/dashboard.php" class="lp-nav-cta">Dashboard</a>
  <?php else: ?>
    <a href="/login.php" class="lp-nav-cta">Accedi</a>
  <?php endif; ?>
</nav>

<section class="lp-hero" id="come-funziona">
  <div>
    <h1>Una pagina, <span class="hl">tutta</span><br>la tua musica.</h1>
    <p class="lp-sub">
      Con myBand crei in pochi minuti la tua pagina artista: link a Spotify e social, brani in
      ascolto, prossimi concerti, blog e contatti booking. Tutto da un unico posto, sempre
      aggiornato.
    </p>
    <div class="lp-cta-row">
      <a href="/request_access.php" class="lp-btn-dark">Richiedi l'accesso</a>
      <a href="/login.php" class="lp-btn-outline">Accedi</a>
    </div>
  </div>
  <div class="lp-illustration">
    <div class="lp-float-card lp-float-1">🎵 Brani</div>
    <div class="lp-float-card lp-float-2">📅 Eventi</div>
    <div class="lp-float-card lp-float-3">✨ Segui</div>
    <div class="lp-phone">
      <div class="lp-avatar"></div>
      <div class="lp-name">La Tua Band</div>
      <div class="lp-handle">@latuaband</div>
      <div class="lp-pill-row"><span>Home</span><span>Timeline</span><span>Blog</span></div>
      <div class="lp-link-btn" style="background:#FFD6A5;">Sitoweb Personale</div>
      <div class="lp-link-btn" style="background:#CAFFBF;">Ascolta su Spotify</div>
      <div class="lp-link-btn" style="background:#9BF6FF;">Prossimo concerto</div>
    </div>
  </div>
</section>

<section class="lp-features" id="funzionalita">
  <div class="lp-feature">
    <div class="lp-feature-icon">🎧</div>
    <h3>Brani da Spotify</h3>
    <p>Cerca e mostra i tuoi brani direttamente dal catalogo Spotify, con recensioni dei fan.</p>
  </div>
  <div class="lp-feature">
    <div class="lp-feature-icon">🔗</div>
    <h3>Tutti i link in un posto</h3>
    <p>Spotify, YouTube, Instagram, TikTok, sito personale — un solo Linktree musicale.</p>
  </div>
  <div class="lp-feature">
    <div class="lp-feature-icon">📅</div>
    <h3>Eventi e concerti</h3>
    <p>Annuncia le prossime date con link ai biglietti, sempre visibili sulla tua pagina.</p>
  </div>
  <div class="lp-feature">
    <div class="lp-feature-icon">💬</div>
    <h3>Timeline e community</h3>
    <p>Pubblica aggiornamenti, segui altri artisti, costruisci la tua rete su myBand.</p>
  </div>
</section>

<section class="lp-final-cta">
  <h2>myBand è ad accesso su invito</h2>
  <p>Raccontaci chi sei: valutiamo ogni richiesta personalmente.</p>
  <a href="/request_access.php" class="lp-btn-dark">Richiedi l'accesso</a>
</section>

<footer class="lp-footer">myband.it &middot; piattaforma per musicisti indipendenti</footer>
</body>
</html>
