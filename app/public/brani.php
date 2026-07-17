<?php
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.id, u.slug, p.display_name, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_show_id, p.youtube_channel_id
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$slug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    exit('Pagina non trovata.');
}

$tracks = getDB()->prepare('SELECT * FROM audio_tracks WHERE user_id=? ORDER BY sort_order ASC, id DESC');
$tracks->execute([$artist['id']]);
$tracks = $tracks->fetchAll();

$pageUrl = siteUrl('/' . $slug . '/brani');
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Brani di <?= e($artist['display_name']) ?> — myband.it</title>
<meta property="og:type" content="website">
<meta property="og:title" content="Brani di <?= e($artist['display_name']) ?>">
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
  <?= publicProfileHeader($artist, 'brani') ?>

  <?php if (!$tracks): ?>
    <div class="card">Nessun brano caricato ancora.</div>
  <?php endif; ?>

  <?php foreach ($tracks as $t): ?>
    <div class="card" style="display:flex;gap:14px;align-items:center;">
      <?php if ($t['cover_path']): ?>
        <img src="/<?= e($t['cover_path']) ?>" alt="<?= e($t['title']) ?>"
             style="width:72px;height:72px;border-radius:10px;object-fit:cover;flex-shrink:0;">
      <?php else: ?>
        <div style="width:72px;height:72px;border-radius:10px;background:rgba(34,34,59,0.15);flex-shrink:0;"></div>
      <?php endif; ?>
      <div style="flex:1;min-width:0;">
        <a href="/<?= e($slug) ?>/brani/<?= (int)$t['id'] ?>" style="font-weight:700;color:inherit;text-decoration:none;">
          <?= e($t['title']) ?>
        </a>
        <audio controls src="/<?= e($t['file_path']) ?>" style="display:block;width:100%;margin-top:6px;"></audio>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?= renderSiteFooterBar($slug) ?>
</body>
</html>
