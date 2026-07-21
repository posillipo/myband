<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$postId = (int) ($_GET['id'] ?? 0);

$stmt = getDB()->prepare('SELECT u.slug, u.account_type, p.display_name, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_show_id, p.youtube_channel_id, p.genere, tp.*
                          FROM timeline_posts tp
                          JOIN users u ON u.id = tp.user_id
                          JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND tp.id = ? AND u.is_active = 1');
$stmt->execute([$slug, $postId]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Contenuto non trovato.');
}

$artist = [
    'slug' => $slug,
    'display_name' => $post['display_name'],
    'avatar_path' => $post['avatar_path'],
    'spotify_artist_id' => $post['spotify_artist_id'],
    'spotify_show_id' => $post['spotify_show_id'],
    'youtube_channel_id' => $post['youtube_channel_id'],
    'genere' => $post['genere'],
    'account_type' => $post['account_type'],
];

$pageUrl = siteUrl('/' . $slug . '/timeline/' . $postId);
$ogImage = $post['image_path'] ? siteUrl($post['image_path']) : ($post['avatar_path'] ? siteUrl($post['avatar_path']) : null);
$anteprima = $post['testo'] ? textExcerpt($post['testo'], 150) : 'Nuovo aggiornamento su myband.it';
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($post['display_name']) ?> — myband.it</title>
<meta name="description" content="<?= e($anteprima) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($post['display_name']) ?> su myband.it">
<meta property="og:description" content="<?= e($anteprima) ?>">
<meta property="og:url" content="<?= e($pageUrl) ?>">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>
<meta name="twitter:card" content="<?= $ogImage ? 'summary_large_image' : 'summary' ?>">
<link rel="canonical" href="<?= e($pageUrl) ?>">
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<style>:root { --accent: <?= e($post['theme_color'] ?: '#6C5CE7') ?>; }</style>
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body class="colorful-page">
<?= embedTrackingBodyStart() ?>
<div class="container">
  <?= publicProfileHeader($artist, 'timeline') ?>

  <div class="card">
    <?php if ($post['image_path']): ?>
      <img src="/<?= e($post['image_path']) ?>" alt=""
           style="width:100%;max-width:400px;display:block;margin:0 auto 16px;border-radius:14px;object-fit:cover;box-shadow:0 8px 24px rgba(0,0,0,0.15);">
    <?php endif; ?>
    <small style="color:rgba(34,34,59,0.6);"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></small>
    <?php if ($post['testo']): ?>
      <p style="margin-top:8px;font-size:16px;"><?= nl2br(e($post['testo'])) ?></p>
    <?php endif; ?>
  </div>

  <p><a href="/<?= e($slug) ?>/timeline">← Tutta la Timeline di <?= e($post['display_name']) ?></a></p>
</div>
<?= renderFloatingButtons() ?>
<?= renderSiteFooterBar() ?>
</body>
</html>
