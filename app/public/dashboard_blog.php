<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'blog';
$pageTitle = 'Blog';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        if ($title === '' || $content === '') {
            $error = 'Titolo e contenuto sono obbligatori.';
        } else {
            $slug = generateUniquePostSlug((int)$user['id'], $title);
            $excerpt = textExcerpt($content, 200);
            $stmt = getDB()->prepare('INSERT INTO blog_posts (user_id, title, slug, excerpt, content) VALUES (?,?,?,?,?)');
            $stmt->execute([$user['id'], $title, $slug, $excerpt, $content]);
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('DELETE FROM blog_posts WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
    }
    if (!$error) {
        header('Location: /dashboard_blog.php');
        exit;
    }
}

$stmt = getDB()->prepare('SELECT * FROM blog_posts WHERE user_id=? ORDER BY published_at DESC');
$stmt->execute([$user['id']]);
$posts = $stmt->fetchAll();

include __DIR__ . '/_dash_header.php';
?>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" class="card">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="add">
    <label>Titolo post</label>
    <input type="text" name="title" required>
    <label>Contenuto</label>
    <textarea name="content" rows="6" required></textarea>
    <button type="submit" class="btn">Pubblica</button>
  </form>

  <div class="section-title">I tuoi post (<?= count($posts) ?>)</div>
  <?php foreach ($posts as $p): ?>
    <div class="blog-item">
      <div class="date"><?= date('d/m/Y', strtotime($p['published_at'])) ?></div>
      <strong><?= e($p['title']) ?></strong>
      <p style="color:var(--text-muted)"><?= nl2br(e($p['content'])) ?></p>
      <p><a href="<?= e(blogPostUrl($user['slug'], $p)) ?>" target="_blank">myband.it<?= e(blogPostUrl($user['slug'], $p)) ?> ↗</a></p>
      <form method="post" onsubmit="return confirm('Eliminare questo post?');">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <button class="btn small danger" type="submit">Elimina</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
