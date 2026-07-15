<?php
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.id, u.slug, p.display_name, p.avatar_path, p.theme_color
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$slug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    exit('Pagina non trovata.');
}

$events = getDB()->prepare('SELECT * FROM events WHERE user_id=? AND event_date >= NOW() ORDER BY event_date ASC');
$events->execute([$artist['id']]);
$events = $events->fetchAll();

$pageUrl = siteUrl('/' . $slug . '/eventi');
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Concerti di <?= e($artist['display_name']) ?> — myband.it</title>
<meta property="og:type" content="website">
<meta property="og:title" content="Concerti di <?= e($artist['display_name']) ?>">
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
  <?= publicProfileHeader($artist, 'eventi') ?>

  <?php if (!$events): ?>
    <div class="card">Nessun concerto in programma al momento.</div>
  <?php endif; ?>

  <?php foreach ($events as $ev): ?>
    <div class="event-item">
      <div class="date"><?= date('d/m/Y H:i', strtotime($ev['event_date'])) ?></div>
      <strong><?= e($ev['title']) ?></strong>
      <?php if ($ev['venue'] || $ev['city']): ?>
        <div style="color:rgba(34,34,59,0.75);"><?= e($ev['venue']) ?><?= $ev['venue'] && $ev['city'] ? ', ' : '' ?><?= e($ev['city']) ?></div>
      <?php endif; ?>
      <?php if ($ev['ticket_url']): ?>
        <a href="<?= e($ev['ticket_url']) ?>" target="_blank">Biglietti →</a>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?= renderFooterLinks() ?>
<?= renderJoinBar() ?>
</body>
</html>
