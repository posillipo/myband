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
    } elseif ($action === 'toggle_admin' && $id !== (int)$admin['id']) {
        $stmt = getDB()->prepare('UPDATE users SET is_admin = NOT is_admin WHERE id = ?');
        $stmt->execute([$id]);
    } elseif ($action === 'verify_manually' && $id !== (int)$admin['id']) {
        $stmt = getDB()->prepare('UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL WHERE id = ?');
        $stmt->execute([$id]);
    } elseif ($action === 'delete_user' && $id !== (int)$admin['id']) {
        // Elimina l'utente e, a cascata (FK ON DELETE CASCADE), profilo/link/brani/eventi/post/contatti
        $stmt = getDB()->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: /admin_users.php' . ($qs !== '' ? '?' . $qs : ''));
    exit;
}

// Filtri
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(p.display_name LIKE ? OR u.email LIKE ? OR u.slug LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($status === 'active') {
    $where[] = 'u.is_active = 1';
} elseif ($status === 'disabled') {
    $where[] = 'u.is_active = 0';
} elseif ($status === 'unverified') {
    $where[] = 'u.email_verified = 0';
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = getDB()->prepare("SELECT u.id, u.slug, u.email, u.created_at, u.is_active, u.is_admin, u.email_verified, p.display_name,
        (SELECT COUNT(*) FROM links l WHERE l.user_id = u.id) AS n_links,
        (SELECT COUNT(*) FROM audio_tracks t WHERE t.user_id = u.id) AS n_tracks,
        (SELECT COUNT(*) FROM events ev WHERE ev.user_id = u.id) AS n_events,
        (SELECT COUNT(*) FROM blog_posts b WHERE b.user_id = u.id) AS n_posts,
        (SELECT COUNT(*) FROM contact_requests c WHERE c.user_id = u.id) AS n_contacts
    FROM users u JOIN profiles p ON p.user_id = u.id
    {$whereSql}
    ORDER BY u.created_at DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

include __DIR__ . '/_admin_header.php';
?>
  <form method="get" class="card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:1;min-width:200px;">
      <label>Cerca (nome, email, slug)</label>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="es. gianluca">
    </div>
    <div style="min-width:180px;">
      <label>Stato</label>
      <select name="status">
        <option value="all" <?= $status==='all'?'selected':'' ?>>Tutti</option>
        <option value="active" <?= $status==='active'?'selected':'' ?>>Attivi</option>
        <option value="disabled" <?= $status==='disabled'?'selected':'' ?>>Disattivati</option>
        <option value="unverified" <?= $status==='unverified'?'selected':'' ?>>Da verificare</option>
      </select>
    </div>
    <div><button type="submit" class="btn">Filtra</button></div>
  </form>

  <div class="section-title">Musicisti registrati (<?= count($users) ?>)</div>

  <?php foreach ($users as $u): ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
        <div>
          <strong><?= e($u['display_name']) ?></strong>
          <?php if ($u['is_admin']): ?><span style="color:var(--accent);font-size:12px;"> · ADMIN</span><?php endif; ?>
          <?php if (!$u['is_active']): ?><span style="color:#ff8a8a;font-size:12px;"> · DISATTIVATO</span><?php endif; ?>
          <?php if (!$u['email_verified']): ?><span style="color:#ffcf6b;font-size:12px;"> · DA VERIFICARE</span><?php endif; ?>
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
        <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;max-width:260px;">
          <a class="btn small secondary" href="/admin_user_detail.php?id=<?= (int)$u['id'] ?>">Dettagli</a>
          <a class="btn small secondary" href="/admin_user_edit.php?id=<?= (int)$u['id'] ?>">Modifica</a>
          <?php if ($u['id'] != $admin['id']): ?>
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle_active">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small <?= $u['is_active'] ? 'danger' : '' ?>" type="submit">
              <?= $u['is_active'] ? 'Disattiva' : 'Riattiva' ?>
            </button>
          </form>
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="toggle_admin">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small secondary" type="submit">
              <?= $u['is_admin'] ? 'Rimuovi admin' : 'Rendi admin' ?>
            </button>
          </form>
          <?php if (!$u['email_verified']): ?>
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="verify_manually">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small secondary" type="submit">Verifica email</button>
          </form>
          <?php endif; ?>
          <form method="post" onsubmit="return confirm('Eliminare definitivamente questo utente e tutti i suoi contenuti (link, brani, eventi, articoli, contatti)? Azione irreversibile.');">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
            <button class="btn small danger" type="submit">Elimina</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_admin_footer.php'; ?>
