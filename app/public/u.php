<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path, p.theme_color
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

// Separiamo i link riconosciuti come social (icona tonda in alto) dagli altri (pulsante colorato grande)
$socialLinks = [];
$actionLinks = [];
foreach ($links as $l) {
    $platform = detectPlatform($l['url']);
    if ($platform) {
        $socialLinks[] = $l + ['platform' => $platform];
    } else {
        $actionLinks[] = $l;
    }
}
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
<link rel="stylesheet" href="/assets/css/style.css">
<style>
  :root {
    --accent: <?= e($artist['theme_color'] ?: '#6C5CE7') ?>;
    --cf-1: #FFD6A5; --cf-2: #A0C4FF; --cf-3: #BDB2FF;
  }
</style>
<?= embedPrivacyScript() ?>
</head>
<body class="colorful-page">
<div class="container">
  <?= publicProfileHeader($artist, 'home', true) ?>

  <?php if ($socialLinks): ?>
    <div class="social-icons-row">
      <?php foreach ($socialLinks as $l): ?>
        <a class="social-icon-btn" title="<?= e($l['platform']['label']) ?>" target="_blank" rel="noopener"
           href="/link.php?id=<?= (int)$l['id'] ?>"><?= $l['platform']['icon'] ?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($actionLinks): ?>
    <?php foreach ($actionLinks as $i => $l): ?>
      <a class="color-link-btn" style="background:<?= e(COLORFUL_PALETTE[$i % count(COLORFUL_PALETTE)]) ?>;"
         target="_blank" rel="noopener"
         href="/link.php?id=<?= (int)$l['id'] ?>"><?= e($l['label']) ?></a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<footer class="site">Pagina realizzata con <a href="/">myband.it</a></footer>
</body>
</html>
