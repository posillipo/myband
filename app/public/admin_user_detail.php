<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$admin = requireAdmin();
$activeAdminTab = 'users';

$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'toggle_active' && $id !== (int)$admin['id']) {
        $stmt = getDB()->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: /admin_user_detail.php?id=' . $id);
    exit;
}

$stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path
                          FROM users u JOIN profiles p ON p.user_id = u.id WHERE u.id = ?');
$stmt->execute([$id]);
$u = $stmt->fetch();

if (!$u) {
    http_response_code(404);
    exit('Utente non trovato.');
}
$pageTitle = $u['display_name'];

$contacts = getDB()->prepare('SELECT * FROM contact_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 20');
$contacts->execute([$id]);
$contacts = $contacts->fetchAll();

include __DIR__ . '/_admin_header.php';
?>
  <p><a href="/admin_users.php">← Tutti gli utenti</a></p>

  <div class="card">
    <h2 style="margin-top:0;"><?= e($u['display_name']) ?></h2>
    <p style="color:var(--text-muted)">
      Email: <?= e($u['email']) ?><br>
      Pagina pubblica: <a href="/<?= e($u['slug']) ?>" target="_blank">myband.it/<?= e($u['slug']) ?></a><br>
      Iscritto il: <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?><br>
      Stato: <?= $u['is_active'] ? 'Attivo' : 'Disattivato' ?><?= $u['is_admin'] ? ' · Amministratore' : '' ?>
    </p>
    <?php if ($u['bio']): ?><p><em><?= nl2br(e($u['bio'])) ?></em></p><?php endif; ?>

    <?php if ($u['id'] != $admin['id']): ?>
    <form method="post" onsubmit="return confirm('<?= $u['is_active'] ? 'Disattivare' : 'Riattivare' ?> questo account?');">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="toggle_active">
      <button class="btn <?= $u['is_active'] ? 'danger' : '' ?>" type="submit">
        <?= $u['is_active'] ? 'Disattiva account' : 'Riattiva account' ?>
      </button>
    </form>
    <?php endif; ?>
  </div>

  <div class="section-title">Richieste di contatto ricevute (<?= count($contacts) ?>)</div>
  <?php if (!$contacts): ?>
    <div class="card">Nessuna richiesta ricevuta.</div>
  <?php endif; ?>
  <?php foreach ($contacts as $c): ?>
    <div class="card">
      <strong><?= e($c['sender_name']) ?></strong>
      <small style="color:var(--text-muted)"> &lt;<?= e($c['sender_email']) ?>&gt; · <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></small>
      <p><?= nl2br(e($c['message'])) ?></p>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_admin_footer.php'; ?>
