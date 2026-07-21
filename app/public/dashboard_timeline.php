<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'timeline';
$pageTitle = 'La mia Timeline';

$followedIds = getFollowedUserIds((int) $user['id']);
$feed = getTimelineFeedForUsers($followedIds, 50);

include __DIR__ . '/_dash_header.php';
?>
  <div class="card">
    <strong>Come funziona</strong>
    <p style="color:var(--text-muted)">
      Qui vedi, in un unico flusso, gli ultimi contenuti pubblicati dai profili che segui —
      articoli blog, brani caricati, eventi annunciati, aggiornamenti brevi. Per iniziare, vai
      sulla pagina pubblica di una band e clicca "Segui".
    </p>
  </div>

  <?php if (!$followedIds): ?>
    <div class="alert error">Non segui ancora nessun profilo.</div>
  <?php elseif (!$feed): ?>
    <div class="card">I profili che segui non hanno ancora pubblicato nulla.</div>
  <?php else: ?>
    <?php foreach ($feed as $item): ?>
      <a href="<?= e($item['url']) ?>" class="link-item" style="display:flex;gap:12px;align-items:center;text-decoration:none;color:inherit;">
        <?php if ($item['cover']): ?>
          <img src="/<?= e($item['cover']) ?>" style="width:56px;height:56px;border-radius:8px;object-fit:cover;flex-shrink:0;">
        <?php elseif ($item['avatar']): ?>
          <img src="/<?= e($item['avatar']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;flex-shrink:0;">
        <?php endif; ?>
        <div style="flex:1;min-width:0;">
          <small style="color:var(--text-muted);text-transform:uppercase;">
            <?= ['blog' => '📝 Articolo', 'brano' => '🎵 Brano', 'evento' => '📅 Evento', 'pensiero' => '💬 Aggiornamento'][$item['tipo']] ?? '' ?>
            · <?= e($item['display_name']) ?>
          </small>
          <br>
          <strong><?= e($item['titolo']) ?></strong>
          <br>
          <small style="color:var(--text-muted)">
            <?= date('d/m/Y', strtotime($item['data'])) ?>
            <?php if ($item['tipo'] === 'evento' && !empty($item['evento_quando'])): ?>
              · si terrà il <?= date('d/m/Y', strtotime($item['evento_quando'])) ?>
            <?php endif; ?>
          </small>
        </div>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
