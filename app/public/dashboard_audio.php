<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'audio';
$pageTitle = 'Brani';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        if ($title === '') {
            $error = 'Inserisci un titolo per il brano.';
        } elseif (empty($_FILES['audio']['name'])) {
            $error = 'Seleziona un file audio.';
        } else {
            $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['mp3','wav','ogg','m4a'], true)) {
                $error = 'Formato non supportato (usa mp3, wav, ogg o m4a).';
            } elseif ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Errore durante il caricamento del file.';
            } else {
                $fname = bin2hex(random_bytes(6)) . '.' . $ext;
                $audioDir = __DIR__ . '/uploads/audio/' . $user['slug'];
                if (!is_dir($audioDir)) {
                    mkdir($audioDir, 0775, true);
                }
                $dest = $audioDir . '/' . $fname;
                if (move_uploaded_file($_FILES['audio']['tmp_name'], $dest)) {
                    $coverPath = handleCoverUpload($user['slug']);
                    $stmt = getDB()->prepare('INSERT INTO audio_tracks (user_id, title, file_path, cover_path) VALUES (?,?,?,?)');
                    $stmt->execute([$user['id'], $title, 'uploads/audio/' . $user['slug'] . '/' . $fname, $coverPath]);
                } else {
                    $error = 'Impossibile salvare il file.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('SELECT file_path, cover_path FROM audio_tracks WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
        if ($row = $stmt->fetch()) {
            @unlink(__DIR__ . '/' . $row['file_path']);
            if ($row['cover_path']) {
                @unlink(__DIR__ . '/' . $row['cover_path']);
            }
            $stmt = getDB()->prepare('DELETE FROM audio_tracks WHERE id=? AND user_id=?');
            $stmt->execute([$id, $user['id']]);
        }
    }
    if (!$error) {
        header('Location: /dashboard_audio.php');
        exit;
    }
}

$stmt = getDB()->prepare('SELECT * FROM audio_tracks WHERE user_id=? ORDER BY sort_order ASC, id DESC');
$stmt->execute([$user['id']]);
$tracks = $stmt->fetchAll();

include __DIR__ . '/_dash_header.php';
?>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" enctype="multipart/form-data" class="card">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="add">
    <label>Titolo brano</label>
    <input type="text" name="title" required>
    <label>File audio (mp3, wav, ogg, m4a — max 20MB)</label>
    <input type="file" name="audio" accept="audio/*" required>
    <label>Copertina del brano (opzionale, jpg/png/webp)</label>
    <input type="file" name="cover" accept="image/*">
    <button type="submit" class="btn">Carica brano</button>
  </form>

  <div class="section-title">I tuoi brani (<?= count($tracks) ?>)</div>
  <?php foreach ($tracks as $t): ?>
    <div class="card" style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;">
      <?php if ($t['cover_path']): ?>
        <img src="/<?= e($t['cover_path']) ?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;flex-shrink:0;">
      <?php else: ?>
        <div style="width:64px;height:64px;border-radius:8px;background:#26262f;flex-shrink:0;"></div>
      <?php endif; ?>
      <div style="flex:1;min-width:200px;">
        <strong><?= e($t['title']) ?></strong>
        <audio controls src="/<?= e($t['file_path']) ?>" style="display:block;margin-top:6px;"></audio>
      </div>
      <form method="post" onsubmit="return confirm('Eliminare questo brano?');">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
        <button class="btn small danger" type="submit">Elimina</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
