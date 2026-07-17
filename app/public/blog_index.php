<?php
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$userSlug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.id, u.slug, p.display_name, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_show_id, p.youtube_channel_id
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$userSlug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    exit('Pagina non trovata.');
}

$stmt = getDB()->prepare('SELECT * FROM blog_posts WHERE user_id=? ORDER BY published_at DESC');
$stmt->execute([$artist['id']]);
$posts = $stmt->fetchAll();

$pageUrl = siteUrl('/' . $userSlug . '/blog');
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Blog di <?= e($artist['display_name']) ?> — myband.it</title>
<meta property="og:type" content="website">
<meta property="og:title" content="Blog di <?= e($artist['display_name']) ?>">
<meta property="og:url" content="<?= e($pageUrl) ?>">
<link rel="canonical" href="<?= e($pageUrl) ?>">
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<style>:root { --accent: <?= e($artist['theme_color'] ?: '#6C5CE7') ?>; }</style>
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body class="colorful-page">
<?= embedTrackingBodyStart() ?>
<div class="container">
  <?= publicProfileHeader($artist, 'blog') ?>

  <?php if (!$posts): ?>
    <div class="card">Nessun articolo pubblicato ancora.</div>
  <?php endif; ?>

  <?php foreach ($posts as $i => $p): ?>
    <a href="<?= e(blogPostUrl($userSlug, $p)) ?>" class="color-link-btn"
       style="background:<?= e(COLORFUL_PALETTE[$i % count(COLORFUL_PALETTE)]) ?>; display:flex; align-items:center; gap:12px; text-align:left;">
      <?php if ($p['cover_path']): ?>
        <img src="/<?= e($p['cover_path']) ?>" style="width:56px;height:56px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <?php endif; ?>
      <span style="flex:1;min-width:0;">
        <strong style="display:block;"><?= e($p['title']) ?></strong>
        <small style="opacity:.75;"><?= date('d/m/Y', strtotime($p['published_at'])) ?></small>
      </span>
    </a>
  <?php endforeach; ?>
</div>
<?= renderSiteFooterBar() ?>
</body>
</html>
