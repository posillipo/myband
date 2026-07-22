<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'post';
$pageTitle = 'Pubblica';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $testo = trim($_POST['testo'] ?? '');
        $imagePath = handleCoverUpload($user['slug'], 'image');

        if ($testo === '' && !$imagePath) {
            $error = 'Scrivi qualcosa o allega una foto.';
        } else {
            $stmt = getDB()->prepare('INSERT INTO timeline_posts (user_id, testo, image_path) VALUES (?,?,?)');
            $stmt->execute([$user['id'], $testo ?: null, $imagePath]);

            $anteprima = $testo !== '' ? textExcerpt($testo, 80) : 'Nuova foto pubblicata';
            $timelineUrl = siteUrl('/' . $user['slug'] . '/timeline');
            notifyFollowersNewContent((int) $user['id'], $user['display_name'], $user['slug'], 'timeline', $anteprima, $timelineUrl);
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('SELECT image_path FROM timeline_posts WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
        if ($row = $stmt->fetch()) {
            deleteCoverFile($row['image_path']);
        }
        $stmt = getDB()->prepare('DELETE FROM timeline_posts WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
    }
}

$stmt = getDB()->prepare('SELECT * FROM timeline_posts WHERE user_id=? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$posts = $stmt->fetchAll();

include __DIR__ . '/_dash_header.php';
?>
  <details class="help-box">
    <summary>ℹ️ Come funziona</summary>
    <p style="color:var(--text-muted)">
      Un modo rapido per condividere un pensiero, un annuncio breve, o una foto — senza dover
      scrivere un articolo completo come nel Blog. Compare subito nella tua Timeline pubblica e
      in quella di chi ti segue.
    </p>
  </details>

  <?php if (!empty($error)): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="add">
    <label>Cosa vuoi condividere?</label>
    <textarea name="testo" rows="3" placeholder="Scrivilo qui..."></textarea>
    <label>Foto (opzionale)</label>
    <input type="file" name="image" accept="image/*">
    <button type="submit" class="btn">Pubblica</button>
  </form>

  <div class="section-title">I tuoi aggiornamenti (<?= count($posts) ?>)</div>
  <?php foreach ($posts as $p): ?>
    <div class="card" style="display:flex;gap:14px;align-items:flex-start;">
      <?php if ($p['image_path']): ?>
        <img src="/<?= e($p['image_path']) ?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <?php endif; ?>
      <div style="flex:1;min-width:0;">
        <small style="color:var(--text-muted)"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></small>
        <?php if ($p['testo']): ?><p style="margin:4px 0;"><?= nl2br(e($p['testo'])) ?></p><?php endif; ?>
        <a href="/<?= e($user['slug']) ?>/timeline/<?= (int)$p['id'] ?>" target="_blank" style="font-size:13px;">Vedi pagina pubblica ↗</a>
        <form method="post" onsubmit="return confirm('Eliminare questo aggiornamento?');">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          <button class="btn small danger" type="submit">Elimina</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
