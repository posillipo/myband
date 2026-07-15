<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'links';
$pageTitle = 'Link';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $label = trim($_POST['label'] ?? '');
        $url = trim($_POST['url'] ?? '');
        if ($label !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
            $stmt = getDB()->prepare('INSERT INTO links (user_id, label, url, sort_order) VALUES (?,?,?, (SELECT n FROM (SELECT COALESCE(MAX(sort_order),0)+1 AS n FROM links WHERE user_id=?) t))');
            $stmt->execute([$user['id'], $label, $url, $user['id']]);
        } else {
            $error = 'Inserisci un\'etichetta e un URL valido.';
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('DELETE FROM links WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('UPDATE links SET is_active = NOT is_active WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
    } elseif ($action === 'move_up' || $action === 'move_down') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('SELECT id, sort_order FROM links WHERE user_id=? ORDER BY sort_order ASC, id ASC');
        $stmt->execute([$user['id']]);
        $all = $stmt->fetchAll();
        $idx = null;
        foreach ($all as $i => $row) {
            if ((int)$row['id'] === $id) { $idx = $i; break; }
        }
        if ($idx !== null) {
            $swapIdx = $action === 'move_up' ? $idx - 1 : $idx + 1;
            if (isset($all[$swapIdx])) {
                $a = $all[$idx];
                $b = $all[$swapIdx];
                getDB()->prepare('UPDATE links SET sort_order=? WHERE id=? AND user_id=?')->execute([$b['sort_order'], $a['id'], $user['id']]);
                getDB()->prepare('UPDATE links SET sort_order=? WHERE id=? AND user_id=?')->execute([$a['sort_order'], $b['id'], $user['id']]);
            }
        }
    }
    header('Location: /dashboard_links.php');
    exit;
}

$stmt = getDB()->prepare('SELECT * FROM links WHERE user_id=? ORDER BY sort_order ASC, id ASC');
$stmt->execute([$user['id']]);
$links = $stmt->fetchAll();

include __DIR__ . '/_dash_header.php';
?>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" class="card">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="add">
    <label>Etichetta (es. "Ascolta su Spotify", "Instagram")</label>
    <input type="text" name="label" required>
    <label>URL</label>
    <input type="url" name="url" placeholder="https://..." required>
    <button type="submit" class="btn">Aggiungi link</button>
  </form>

  <div class="section-title">I tuoi link (<?= count($links) ?>)</div>
  <p style="color:var(--text-muted);font-size:13px;">
    Le icone social (Spotify, Instagram, Facebook, TikTok, YouTube, LinkedIn) vengono riconosciute
    automaticamente e mostrate come icona in alto — solo la <strong>prima</strong> di ciascuna
    piattaforma; eventuali duplicati restano tra i pulsanti qui sotto. L'ordine dei pulsanti nella
    pagina pubblica segue l'ordine in cui li disponi qui (usa le frecce ▲▼ per riordinare).
  </p>
  <?php foreach ($links as $i => $l): ?>
    <div class="link-item">
      <div>
        <strong><?= e($l['label']) ?></strong><br>
        <small style="color:var(--text-muted)"><?= e($l['url']) ?> · <?= (int)$l['click_count'] ?> click</small>
      </div>
      <div style="display:flex;gap:6px;align-items:center;">
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="move_up">
          <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
          <button class="btn small secondary" type="submit" <?= $i === 0 ? 'disabled style="opacity:.3;cursor:default;"' : '' ?>>▲</button>
        </form>
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="move_down">
          <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
          <button class="btn small secondary" type="submit" <?= $i === count($links) - 1 ? 'disabled style="opacity:.3;cursor:default;"' : '' ?>>▼</button>
        </form>
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="toggle">
          <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
          <button class="btn small secondary" type="submit"><?= $l['is_active'] ? 'Nascondi' : 'Mostra' ?></button>
        </form>
        <form method="post" onsubmit="return confirm('Eliminare questo link?');">
          <?= csrfField() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
          <button class="btn small danger" type="submit">Elimina</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
