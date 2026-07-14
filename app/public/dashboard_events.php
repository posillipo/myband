<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'events';
$pageTitle = 'Concerti';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $date = $_POST['event_date'] ?? '';
        $ticketUrl = trim($_POST['ticket_url'] ?? '');
        if ($title === '' || $date === '') {
            $error = 'Titolo e data sono obbligatori.';
        } else {
            $stmt = getDB()->prepare('INSERT INTO events (user_id, title, venue, city, event_date, ticket_url) VALUES (?,?,?,?,?,?)');
            $stmt->execute([$user['id'], $title, $venue ?: null, $city ?: null, $date, $ticketUrl ?: null]);
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getDB()->prepare('DELETE FROM events WHERE id=? AND user_id=?');
        $stmt->execute([$id, $user['id']]);
    }
    if (!$error) {
        header('Location: /dashboard_events.php');
        exit;
    }
}

$stmt = getDB()->prepare('SELECT * FROM events WHERE user_id=? ORDER BY event_date ASC');
$stmt->execute([$user['id']]);
$events = $stmt->fetchAll();

include __DIR__ . '/_dash_header.php';
?>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" class="card">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="add">
    <label>Nome evento</label>
    <input type="text" name="title" required>
    <label>Locale</label>
    <input type="text" name="venue">
    <label>Città</label>
    <input type="text" name="city">
    <label>Data e ora</label>
    <input type="datetime-local" name="event_date" required>
    <label>Link biglietti (opzionale)</label>
    <input type="url" name="ticket_url" placeholder="https://...">
    <button type="submit" class="btn">Aggiungi concerto</button>
  </form>

  <div class="section-title">Prossimi concerti (<?= count($events) ?>)</div>
  <?php foreach ($events as $ev): ?>
    <div class="event-item">
      <div class="date"><?= date('d/m/Y H:i', strtotime($ev['event_date'])) ?></div>
      <strong><?= e($ev['title']) ?></strong>
      <?php if ($ev['venue'] || $ev['city']): ?>
        <div style="color:var(--text-muted)"><?= e($ev['venue']) ?><?= $ev['venue'] && $ev['city'] ? ', ' : '' ?><?= e($ev['city']) ?></div>
      <?php endif; ?>
      <form method="post" style="margin-top:8px;" onsubmit="return confirm('Eliminare questo evento?');">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int)$ev['id'] ?>">
        <button class="btn small danger" type="submit">Elimina</button>
      </form>
    </div>
  <?php endforeach; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
