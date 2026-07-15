<?php
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$userSlug = $_GET['slug'] ?? '';
$postToken = $_GET['post'] ?? '';

if (!preg_match('/^\d{4}\.\d{2}\.\d{2}\.(.+)$/', $postToken, $m)) {
    http_response_code(404);
    exit('Articolo non trovato.');
}
$postSlug = $m[1];

$stmt = getDB()->prepare('SELECT u.slug AS user_slug, p.display_name, p.avatar_path, p.theme_color, b.*
                          FROM blog_posts b
                          JOIN users u ON u.id = b.user_id
                          JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND b.slug = ? AND u.is_active = 1');
$stmt->execute([$userSlug, $postSlug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    exit('Articolo non trovato.');
}

// Per riusare l'header condiviso serve un array "artista" con le chiavi attese
$artist = [
    'slug' => $userSlug,
    'display_name' => $post['display_name'],
    'avatar_path' => $post['avatar_path'],
];

$permalink = siteUrl(blogPostUrl($userSlug, $post));
$ogImage = $post['avatar_path'] ? siteUrl($post['avatar_path']) : null;
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($post['title']) ?> — <?= e($post['display_name']) ?></title>
<meta name="description" content="<?= e($post['excerpt'] ?: textExcerpt($post['content'])) ?>">

<meta property="og:type" content="article">
<meta property="og:title" content="<?= e($post['title']) ?>">
<meta property="og:description" content="<?= e($post['excerpt'] ?: textExcerpt($post['content'])) ?>">
<meta property="og:url" content="<?= e($permalink) ?>">
<meta property="og:site_name" content="myband.it">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= e($post['title']) ?>">
<meta name="twitter:description" content="<?= e($post['excerpt'] ?: textExcerpt($post['content'])) ?>">

<link rel="canonical" href="<?= e($permalink) ?>">
<link rel="stylesheet" href="/assets/css/style.css">
<style>:root { --accent: <?= e($post['theme_color'] ?: '#6C5CE7') ?>; }</style>
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body class="colorful-page">
<?= embedTrackingBodyStart() ?>
<div class="container">
  <?= publicProfileHeader($artist, 'blog') ?>

  <article class="blog-item" style="border-bottom:none;">
    <div class="date"><?= date('d/m/Y', strtotime($post['published_at'])) ?></div>
    <h2><?= e($post['title']) ?></h2>
    <div><?= nl2br(e($post['content'])) ?></div>
  </article>

  <div class="card" style="margin-top:24px;">
    <strong>Condividi questo articolo</strong><br>
    <small style="color:rgba(34,34,59,0.75);"><?= e($permalink) ?></small>
  </div>
</div>
<?= renderFooterLinks() ?>
<footer class="site">Pagina realizzata con <a href="/">myband.it</a></footer>
<?= renderJoinBar() ?>
</body>
</html>
