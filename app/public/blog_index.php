<?php
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$userSlug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.id, u.slug, p.display_name, p.avatar_path
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
<link rel="stylesheet" href="/assets/css/style.css">
<?= embedPrivacyScript() ?>
</head>
<body>
<div class="container">
  <div class="section-title"><a href="/<?= e($userSlug) ?>">← <?= e($artist['display_name']) ?></a></div>
  <h1>Blog</h1>

  <?php if (!$posts): ?>
    <div class="card">Nessun articolo pubblicato ancora.</div>
  <?php endif; ?>

  <?php foreach ($posts as $p): ?>
    <div class="blog-item">
      <div class="date"><?= date('d/m/Y', strtotime($p['published_at'])) ?></div>
      <a href="<?= e(blogPostUrl($userSlug, $p)) ?>"><strong><?= e($p['title']) ?></strong></a>
      <p style="color:var(--text-muted)"><?= e($p['excerpt'] ?: textExcerpt($p['content'])) ?></p>
    </div>
  <?php endforeach; ?>
</div>
<footer class="site">Pagina realizzata con <a href="/">myband.it</a></footer>
</body>
</html>
