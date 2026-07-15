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
<link rel="stylesheet" href="/assets/css/style.css">
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
    <div class="card">
      <strong><?= e($t['title']) ?></strong>
      <audio controls src="/<?= e($t['file_path']) ?>"></audio>
    </div>
  <?php endforeach; ?>
</div>
<?= renderFooterLinks() ?>
<footer class="site">Pagina realizzata con <a href="/">myband.it</a></footer>
<?= renderJoinBar() ?>
</body>
</html>
