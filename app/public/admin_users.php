<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$admin = requireAdmin();
$activeAdminTab = 'users';
$pageTitle = 'Utenti iscritti';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'toggle_active' && $id !== (int)$admin['id']) {
        $stmt = getDB()->prepare('UPDATE users SET is_active = NOT is_active WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: /admin_users.php');
    exit;
}

$stmt = getDB()->query('SELECT u.id, u.slug, u.email, u.created_at, u.is_active, u.is_admin, p.display_name,
        (SELECT COUNT(*) FROM links l WHERE l.user_id = u.id) AS n_links,
        (SELECT COUNT(*) FROM audio_tracks t WHERE t.user_id = u.id) AS n_tracks,
        (SELECT COUNT(*) FROM events ev WHERE ev.user_id = u.id) AS n_events,
        (SELECT COUNT(*) FROM blog_posts b WHERE b.user_id = u.id) AS n_posts,
        (SELECT COUNT(*) FROM contact_requests c WHERE c.user_id = u.id) AS n_contacts
    FROM users u JOIN profiles p ON p.user_id = u.id
    ORDER BY u.created_at DESC');
$users = $stmt->fetchAll();

include __DIR__ . '/_admin_header.php';
?>
  <div class="section-title">Musicisti registrati (<?= count($users) ?>)</div>

  <?php foreach ($users as $u): ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
        <div>
          <strong><?= e($u['display_name']) ?></strong>
          <?php if ($u['is_admin']): ?><span style="color:var(--accent);font-size:12px;"> · ADMIN</span><?php endif; ?>
          <?php if (!$u['is_active']): ?><span style="color:#ff8a8a;font-size:12px;"> · DISATTIVATO</span><?php endif; ?>
          <br>
          <small style="color:var(--text-muted)">
            <?= e($u['email']) ?> · myband.it/<?= e($u['slug']) ?> · iscritto il <?= date('d/m/Y', strtotime($u['created_at'])) ?>
          </small>
          <br>
          <small style="color:var(--text-muted)">
            <?= (int)$u['n_links'] ?> link · <?= (int)$u['n_tracks'] ?> brani · <?= (int)$u['n_events'] ?> concerti ·
            <?= (int)$u['n_posts'] ?> post blog · <?= (int)$u['n_contacts'] ?> richieste contatto
          </small>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0;">
          <a class="btn small secondary" href="/admin_user_detail.php?id=<?= (int)$u['id'] ?>">Dettagli</a>
          <?php if ($u['id'] != $admin['id']): ?>
          <form method="post" onsubmit="return confirm('<?= $u['is_active'] ? 'Disattivare' : 'Riattivare' ?> questo account?');">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle_active">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small <?= $u['is_active'] ? 'danger' : '' ?>" type="submit">
              <?= $u['is_active'] ? 'Disattiva' : 'Riattiva' ?>
            </button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_admin_footer.php'; ?>
