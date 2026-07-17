<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path, p.theme_color, p.spotify_artist_id
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$slug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    echo '<h1 style="font-family:sans-serif;text-align:center;margin-top:80px;">Pagina non trovata</h1>';
    exit;
}

$uid = $artist['id'];

$links = getDB()->prepare('SELECT * FROM links WHERE user_id=? AND is_active=1 ORDER BY sort_order ASC, id ASC');
$links->execute([$uid]);
$links = $links->fetchAll();

$pageUrl = siteUrl('/' . $slug);
$ogImage = $artist['avatar_path'] ? siteUrl($artist['avatar_path']) : null;
$ogDescription = $artist['bio'] ? textExcerpt($artist['bio']) : ('La pagina di ' . $artist['display_name'] . ' su myband.it');

// Separiamo i link riconosciuti come social (icona tonda in alto, una sola per piattaforma)
// dagli altri (pulsante colorato grande) — include eventuali social ripetuti
[$socialLinks, $actionLinks] = splitSocialAndActionLinks($links);

$followerCount = getFollowerCount($uid);
$followMsg = $_GET['follow_msg'] ?? '';
$followErr = ($_GET['follow_err'] ?? '0') === '1';
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($artist['display_name']) ?> — myband.it</title>
<meta name="description" content="<?= e($ogDescription) ?>">

<!-- Open Graph / condivisione social -->
<meta property="og:type" content="profile">
<meta property="og:title" content="<?= e($artist['display_name']) ?>">
<meta property="og:description" content="<?= e($ogDescription) ?>">
<meta property="og:url" content="<?= e($pageUrl) ?>">
<meta property="og:site_name" content="myband.it">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= e($artist['display_name']) ?>">
<meta name="twitter:description" content="<?= e($ogDescription) ?>">

<link rel="canonical" href="<?= e($pageUrl) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<style>
  :root {
    --accent: <?= e($artist['theme_color'] ?: '#6C5CE7') ?>;
    --cf-1: #FFD6A5; --cf-2: #A0C4FF; --cf-3: #BDB2FF;
  }
</style>
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body class="colorful-page">
<?= embedTrackingBodyStart() ?>
<div class="container">
  <?= publicProfileHeader($artist, 'home', true) ?>

  <?php if ($followMsg): ?>
    <div class="alert <?= $followErr ? 'error' : 'success' ?>"><?= e($followMsg) ?></div>
  <?php endif; ?>

  <div class="card" style="text-align:center;">
    <form method="post" action="/follow.php" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;align-items:center;">
      <?= csrfField() ?>
      <input type="hidden" name="slug" value="<?= e($slug) ?>">
      <input type="email" name="email" placeholder="La tua email" required style="flex:1;min-width:180px;max-width:280px;margin-bottom:0;">
      <button type="submit" class="btn">Segui <?= e($artist['display_name']) ?></button>
    </form>
    <p style="color:rgba(34,34,59,0.7);font-size:13px;margin-top:10px;margin-bottom:0;">
      <?php if ($followerCount > 0): ?>
        <?= $followerCount ?> <?= $followerCount === 1 ? 'persona segue' : 'persone seguono' ?> questo artista
      <?php else: ?>
        Ricevi una notifica quando pubblica novità
      <?php endif; ?>
    </p>
  </div>

  <?php if ($socialLinks): ?>
    <div class="social-icons-row">
      <?php foreach ($socialLinks as $l): ?>
        <a class="social-icon-btn" title="<?= e($l['platform']['label']) ?>" target="_blank" rel="noopener"
           href="/link.php?id=<?= (int)$l['id'] ?>"><i class="<?= e($l['platform']['icon_class']) ?>"></i></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($actionLinks): ?>
    <?php foreach ($actionLinks as $i => $l): ?>
      <a class="color-link-btn" style="background:<?= e(COLORFUL_PALETTE[$i % count(COLORFUL_PALETTE)]) ?>; display:flex; align-items:center; gap:12px; text-align:left;"
         target="_blank" rel="noopener"
         href="/link.php?id=<?= (int)$l['id'] ?>">
        <?php if ($l['cover_path']): ?>
          <img src="/<?= e($l['cover_path']) ?>" style="width:40px;height:40px;border-radius:8px;object-fit:cover;flex-shrink:0;">
        <?php endif; ?>
        <span><?= e($l['label']) ?></span>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?= renderFooterLinks() ?>
<?= renderJoinBar() ?>
</body>
</html>
