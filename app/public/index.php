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
<title>myband.it — Il Link in Bio per musicisti indipendenti</title>
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

  /* Confronto profili (Visitatore / Band Manager / Fan / Etichetta) — mobile-first:
     le regole di base sono per schermo stretto (card impilate, nessuno scroll orizzontale);
     i breakpoint con min-width AGGIUNGONO la disposizione a griglia man mano che c'è spazio. */
  .lp-compare { max-width: 1180px; margin: 10px auto 60px; padding: 0 20px; }
  .lp-compare-head { text-align: center; margin-bottom: 28px; }
  .lp-compare-head h2 { font-size: 24px; margin: 0 0 10px; }
  .lp-compare-head p { color: #666; max-width: 560px; margin: 0 auto; font-size: 14.5px; line-height: 1.6; }

  .lp-compare-grid { display: flex; flex-direction: column; gap: 16px; }
  .plan-card { background: #fff; border-radius: 18px; padding: 22px 18px; box-shadow: 0 4px 20px rgba(23,23,43,0.06); position: relative; }
  .plan-card.featured { border: 2px solid rgb(108,92,231); background: rgba(108,92,231,0.03); }
  .plan-badge { position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: rgb(108,92,231); color: #fff; font-size: 10.5px; font-weight: 800; padding: 4px 12px; border-radius: 999px; white-space: nowrap; }
  .plan-head { text-align: center; margin-bottom: 16px; }
  .plan-icon { font-size: 26px; }
  .plan-title { font-weight: 800; font-size: 17px; margin: 6px 0 4px; }
  .plan-desc { font-size: 12.5px; color: #888; line-height: 1.4; }
  .plan-cta { display: inline-block; margin-top: 14px; background: #17172b; color: #fff; padding: 10px 22px; border-radius: 999px; font-weight: 700; font-size: 13px; }
  .plan-group { font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: rgb(108,92,231); margin: 16px 0 6px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
  .plan-group:first-of-type { border-top: none; margin-top: 4px; }
  .plan-features { list-style: none; margin: 0; padding: 0; }
  .plan-features li { display: flex; align-items: flex-start; gap: 8px; font-size: 13.5px; padding: 6px 0; color: #333; line-height: 1.4; }
  .plan-features li.no-item { color: #aaa; }
  .plan-features li .ico { flex: 0 0 16px; text-align: center; font-weight: 800; }
  .plan-features li.yes-item .ico { color: #10ac84; }
  .plan-features li.no-item .ico { color: #ddd; }
  .lp-compare-note { font-size: 12px; color: #999; text-align: center; margin-top: 20px; }

  /* Tablet: 2 colonne */
  @media (min-width: 620px) {
    .lp-compare-grid { display: grid; grid-template-columns: repeat(2, 1fr); align-items: start; gap: 16px; }
  }
  /* Desktop: le 4 card affiancate come nel riferimento a colonne */
  @media (min-width: 1000px) {
    .lp-compare-head h2 { font-size: 32px; }
    .lp-compare-head p { font-size: 15px; }
    .lp-compare-grid { grid-template-columns: repeat(4, 1fr); gap: 18px; }
    .plan-card.featured { transform: translateY(-8px); }
  }
</style>
</head>
<body>
<?= embedTrackingBodyStart() ?>

<nav class="lp-nav">
  <div class="lp-logo"><span class="dot"></span> myBand.it</div>
  <div class="lp-nav-links">
    <a href="#come-funziona">Come funziona</a>
    <a href="#funzionalita">Funzionalità</a>
    <a href="#confronto">Confronta i profili</a>
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
    <p>Spotify, YouTube, Instagram, TikTok, sito personale — un solo Link in Bio musicale.</p>
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

<?php
// Dati del confronto profili: un'unica fonte per generare le 4 card (Visitatore / Band Manager /
// Fan / Etichetta), così l'ordine delle funzionalità resta identico in ognuna senza doverle
// ripetere a mano 4 volte.
$comparePlans = [
    'visitor' => ['icon' => '👀', 'title' => 'Visitatore', 'desc' => 'Scopre le band, nessuna registrazione', 'badge' => null, 'featured' => false],
    'band'    => ['icon' => '🎤', 'title' => 'Band Manager', 'desc' => 'Gestisce la pagina della band', 'badge' => 'Per artisti e band', 'featured' => true],
    'fan'     => ['icon' => '❤️', 'title' => 'Fan', 'desc' => 'Segue e sostiene i suoi artisti', 'badge' => null, 'featured' => false],
    'label'   => ['icon' => '🏷️', 'title' => 'Etichetta', 'desc' => 'Presenta la propria etichetta discografica', 'badge' => null, 'featured' => false],
];
// Nota: il campo testo si chiama "feature" (non "label") perché "label" è già una delle chiavi
// di $comparePlans (colonna Etichetta) — usarlo per entrambi causava una collisione silenziosa
// (la seconda occorrenza della chiave in un array PHP sovrascrive la prima).
$compareGroups = [
    'Pagina pubblica' => [
        ['feature' => 'Pagina pubblica personalizzata (myband.it/tuoslug)', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Link in bio (social, sito web, ecc.)', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Blog con permalink SEO', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Timeline e aggiornamenti', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Brani preferiti (da Spotify)', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Calendario eventi e concerti', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
        ['feature' => 'Discografia Spotify', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
        ['feature' => 'Podcast', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
        ['feature' => 'Video / canale YouTube', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
        ['feature' => 'Form contatti / booking', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
    ],
    'Community' => [
        ['feature' => 'Segui una band via email, senza account', 'visitor' => true, 'band' => false, 'fan' => false, 'label' => false],
        ['feature' => 'Segui altri profili myBand', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Lista "Band che amo"', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Messaggi diretti (chat)', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Vota e recensisci brani e band', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Statistiche follower', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
    ],
    'Gestione avanzata' => [
        ['feature' => 'Inviti per nuovi utenti', 'visitor' => false, 'band' => true, 'fan' => true, 'label' => true],
        ['feature' => 'Team e co-admin sullo stesso profilo', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
        ['feature' => 'Log delle attività', 'visitor' => false, 'band' => true, 'fan' => false, 'label' => true],
    ],
];
?>
<section class="lp-compare" id="confronto">
  <div class="lp-compare-head">
    <h2>Quale profilo fa per te?</h2>
    <p>Su myBand puoi anche solo scoprire le band che ami senza registrarti. Se vuoi di più, scegli
      il profilo più adatto a te: Band Manager, Fan o Etichetta discografica.</p>
  </div>

  <div class="lp-compare-grid">
    <?php foreach ($comparePlans as $planKey => $plan): ?>
    <div class="plan-card<?= $plan['featured'] ? ' featured' : '' ?>">
      <?php if ($plan['badge']): ?><div class="plan-badge"><?= e($plan['badge']) ?></div><?php endif; ?>
      <div class="plan-head">
        <div class="plan-icon"><?= $plan['icon'] ?></div>
        <div class="plan-title"><?= e($plan['title']) ?></div>
        <div class="plan-desc"><?= e($plan['desc']) ?></div>
        <?php if ($planKey !== 'visitor'): ?>
          <a href="/request_access.php" class="plan-cta">Richiedi l'accesso</a>
        <?php endif; ?>
      </div>
      <ul class="plan-features">
        <?php foreach ($compareGroups as $groupName => $rows): ?>
          <li class="plan-group"><?= e($groupName) ?></li>
          <?php foreach ($rows as $row): $included = $row[$planKey]; ?>
            <li class="<?= $included ? 'yes-item' : 'no-item' ?>">
              <span class="ico"><?= $included ? '✓' : '–' ?></span>
              <span><?= e($row['feature']) ?></span>
            </li>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </div>
  <p class="lp-compare-note">myBand è ad accesso su invito: ogni richiesta viene valutata personalmente.</p>
</section>

<section class="lp-final-cta">
  <h2>myBand è ad accesso su invito</h2>
  <p>Raccontaci chi sei: valutiamo ogni richiesta personalmente.</p>
  <a href="/request_access.php" class="lp-btn-dark">Richiedi l'accesso</a>
</section>

<footer class="lp-footer">myband.it &middot; piattaforma per musicisti indipendenti</footer>
</body>
</html>
