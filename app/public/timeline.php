<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.id, u.slug, u.account_type, p.display_name, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_show_id, p.youtube_channel_id, p.genere
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$slug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    exit('Pagina non trovata.');
}

$feed = getTimelineFeedForUsers([$artist['id']], 50);
$pageUrl = siteUrl('/' . $slug . '/timeline');
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Timeline di <?= e($artist['display_name']) ?> — myband.it</title>
<meta property="og:type" content="website">
<meta property="og:title" content="Timeline di <?= e($artist['display_name']) ?>">
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
  <?= publicProfileHeader($artist, 'timeline') ?>

  <?php if (!$feed): ?>
    <div class="card">Nessun contenuto pubblicato ancora.</div>
  <?php else: ?>
    <?php foreach ($feed as $item): ?>
      <a href="<?= e($item['url']) ?>" class="card" style="display:flex;gap:14px;align-items:center;text-decoration:none;color:inherit;">
        <?php if ($item['cover']): ?>
          <?php $coverSrc = str_starts_with($item['cover'], 'http') ? $item['cover'] : '/' . $item['cover']; ?>
          <img src="<?= e($coverSrc) ?>" style="width:64px;height:64px;border-radius:10px;object-fit:cover;flex-shrink:0;">
        <?php endif; ?>
        <div style="flex:1;min-width:0;">
          <small style="color:rgba(34,34,59,0.6);text-transform:uppercase;">
            <?= ['blog' => '📝 Articolo', 'brano' => '🎵 Brano', 'evento' => '📅 Evento', 'pensiero' => '💬 Aggiornamento'][$item['tipo']] ?? '' ?>
          </small>
          <br>
          <strong><?= e($item['titolo']) ?></strong>
          <br>
          <small style="color:rgba(34,34,59,0.6);">
            <?= date('d/m/Y', strtotime($item['data'])) ?>
            <?php if ($item['tipo'] === 'evento' && !empty($item['evento_quando'])): ?>
              · si terrà il <?= date('d/m/Y', strtotime($item['evento_quando'])) ?>
            <?php endif; ?>
          </small>
        </div>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?= renderSiteFooterBar() ?>
</body>
</html>
