<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'timeline';
$pageTitle = 'La mia Timeline';

const DASH_TIMELINE_PAGE_SIZE = 20;
$followedIds = getFollowedUserIds((int) $user['id']);
$feed = getTimelineFeedForUsers($followedIds, DASH_TIMELINE_PAGE_SIZE, 0);

include __DIR__ . '/_dash_header.php';
?>
  <details class="help-box">
    <summary>ℹ️ Come funziona</summary>
    <p style="color:var(--text-muted)">
      Qui vedi, in un unico flusso, gli ultimi contenuti pubblicati dai profili che segui —
      articoli blog, brani caricati, eventi annunciati, aggiornamenti brevi. Per iniziare, vai
      sulla pagina pubblica di una band e clicca "Segui".
    </p>
  </details>

  <?php if (!$followedIds): ?>
    <div class="alert error">Non segui ancora nessun profilo.</div>
  <?php elseif (!$feed): ?>
    <div class="card">I profili che segui non hanno ancora pubblicato nulla.</div>
  <?php else: ?>
    <div id="dash-timeline-feed">
      <?php foreach ($feed as $item): ?>
        <?= renderDashboardTimelineItem($item) ?>
      <?php endforeach; ?>
    </div>
    <div id="dash-timeline-sentinel" style="height:1px;"></div>
    <p id="dash-timeline-loading" style="text-align:center;color:var(--text-muted);display:none;">Caricamento...</p>
    <p id="dash-timeline-end" style="text-align:center;color:var(--text-muted);display:none;">Hai visto tutto.</p>
    <script>
    (function () {
      var offset = <?= count($feed) ?>;
      var pageSize = <?= DASH_TIMELINE_PAGE_SIZE ?>;
      var loading = false;
      var finished = <?= count($feed) < DASH_TIMELINE_PAGE_SIZE ? 'true' : 'false' ?>;
      var feedEl = document.getElementById('dash-timeline-feed');
      var loadingEl = document.getElementById('dash-timeline-loading');
      var endEl = document.getElementById('dash-timeline-end');
      var sentinel = document.getElementById('dash-timeline-sentinel');

      function loadMore() {
        if (loading || finished) return;
        loading = true;
        loadingEl.style.display = 'block';
        fetch('/dashboard_timeline_more.php?offset=' + offset)
          .then(function (r) { return r.json(); })
          .then(function (data) {
            loadingEl.style.display = 'none';
            if (data.html) feedEl.insertAdjacentHTML('beforeend', data.html);
            offset += data.count;
            if (data.count < pageSize) { finished = true; endEl.style.display = 'block'; }
            loading = false;
          })
          .catch(function () { loading = false; loadingEl.style.display = 'none'; });
      }

      if (!finished && 'IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          if (entries[0].isIntersecting) loadMore();
        }).observe(sentinel);
      } else if (finished) {
        endEl.style.display = 'block';
      }
    })();
    </script>
  <?php endif; ?>
<?php include __DIR__ . '/_dash_footer.php'; ?>
