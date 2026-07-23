<?php
// Incluso da tutte le pagine dashboard_*. Richiede $user già caricato e $activeTab impostato.
// Nota: il tema è sempre "chiaro" per scelta di prodotto attuale. La colonna dashboard_theme
// resta nel database per un'eventuale reintroduzione futura della scelta, ma non viene più
// letta qui.
$dashTheme = 'light-theme';
$isBandOrLabel = in_array($user['account_type'] ?? 'band', ['band', 'label'], true);

$stmt = getDB()->prepare('SELECT COUNT(*) c FROM contact_requests WHERE user_id = ? AND is_read = 0');
$stmt->execute([$user['id']]);
$unreadMessages = (int) $stmt->fetch()['c'];
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'Dashboard') ?> — myband.it</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
</head>
<body class="<?= e($dashTheme) ?>">
<div class="navbar">
  <div style="display:flex;align-items:center;gap:14px;">
    <button type="button" id="account-menu-toggle" title="Account e impostazioni"
            style="background:none;border:none;cursor:pointer;font-size:20px;color:inherit;padding:4px;">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="brand"><a href="/">myband<span>.it</span></a></div>
  </div>
  <nav style="display:flex;align-items:center;gap:18px;">
    <?php if (!empty($user['is_admin'])): ?>
      <a href="/admin_dashboard.php" title="Area Admin" style="font-size:17px;"><i class="fa-solid fa-shield-halved"></i></a>
    <?php endif; ?>
    <a href="/dashboard_contacts.php" title="Messaggi" style="position:relative;font-size:17px;">
      <i class="fa-solid fa-bell"></i>
      <?php if ($unreadMessages > 0): ?>
        <span style="position:absolute;top:-7px;right:-9px;background:#e74c3c;color:#fff;border-radius:999px;font-size:10.5px;font-weight:700;padding:1px 5px;line-height:1.3;min-width:16px;text-align:center;">
          <?= $unreadMessages > 9 ? '9+' : $unreadMessages ?>
        </span>
      <?php endif; ?>
    </a>
    <a href="/<?= e($user['slug']) ?>" target="_blank" title="Vedi pagina pubblica" style="display:inline-flex;">
      <?php if (!empty($user['avatar_path'])): ?>
        <img src="/<?= e($user['avatar_path']) ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;">
      <?php else: ?>
        <span style="width:28px;height:28px;border-radius:50%;background:var(--accent);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;">
          <?= e(mb_strtoupper(mb_substr($user['display_name'] ?? '?', 0, 1))) ?>
        </span>
      <?php endif; ?>
    </a>
    <a href="/logout.php">Esci</a>
  </nav>
</div>

<!-- Pannello laterale "Account e impostazioni": Profilo, password, integrazioni esterne -->
<div id="account-menu-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:300;"></div>
<div id="account-menu-sidebar" style="display:none;position:fixed;top:0;left:0;bottom:0;width:260px;max-width:82vw;background:#fff;z-index:301;box-shadow:2px 0 20px rgba(0,0,0,0.2);overflow-y:auto;">
  <div style="padding:18px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center;">
    <strong>Account</strong>
    <button type="button" id="account-menu-close" style="background:none;border:none;font-size:20px;cursor:pointer;">&times;</button>
  </div>
  <div style="padding:8px 0;">
    <a href="/dashboard_profile.php" class="account-sidebar-link <?= $activeTab==='profile'?'active':'' ?>">
      <i class="fa-solid fa-id-card"></i> Profilo e anagrafica
    </a>
    <a href="/dashboard_password.php" class="account-sidebar-link <?= $activeTab==='password'?'active':'' ?>">
      <i class="fa-solid fa-lock"></i> Cambia password
    </a>
    <a href="/dashboard_theme.php" class="account-sidebar-link <?= $activeTab==='theme'?'active':'' ?>">
      <i class="fa-solid fa-palette"></i> Tema grafico
    </a>
    <?php if ($isBandOrLabel): ?>
      <div style="padding:14px 18px 4px;font-size:11.5px;text-transform:uppercase;color:var(--text-muted);">Integrazioni</div>
      <a href="/dashboard_spotify.php" class="account-sidebar-link <?= $activeTab==='spotify'?'active':'' ?>">
        <i class="fa-brands fa-spotify"></i> Account Spotify
        <?php if (!empty($user['spotify_artist_id'])): ?><span class="account-sidebar-dot"></span><?php endif; ?>
      </a>
      <a href="/dashboard_podcast.php" class="account-sidebar-link <?= $activeTab==='podcast'?'active':'' ?>">
        <i class="fa-solid fa-microphone"></i> Account Podcast
        <?php if (!empty($user['spotify_show_id'])): ?><span class="account-sidebar-dot"></span><?php endif; ?>
      </a>
      <a href="/dashboard_youtube.php" class="account-sidebar-link <?= $activeTab==='youtube'?'active':'' ?>">
        <i class="fa-brands fa-youtube"></i> Account YouTube
        <?php if (!empty($user['youtube_channel_id'])): ?><span class="account-sidebar-dot"></span><?php endif; ?>
      </a>
    <?php endif; ?>
  </div>
</div>
<script>
(function () {
  var toggle = document.getElementById('account-menu-toggle');
  var overlay = document.getElementById('account-menu-overlay');
  var sidebar = document.getElementById('account-menu-sidebar');
  var closeBtn = document.getElementById('account-menu-close');
  function open() { overlay.style.display = 'block'; sidebar.style.display = 'block'; }
  function close() { overlay.style.display = 'none'; sidebar.style.display = 'none'; }
  toggle.addEventListener('click', open);
  overlay.addEventListener('click', close);
  closeBtn.addEventListener('click', close);
})();
</script>

<div class="container">
  <div class="tabs">
    <a href="/dashboard_timeline.php" class="<?= $activeTab==='timeline'?'active':'' ?>">TIMELINE</a>
    <a href="/dashboard_post.php" class="<?= $activeTab==='post'?'active':'' ?>">Pubblica</a>
    <a href="/dashboard_fan_bands.php" class="<?= $activeTab==='fan_bands'?'active':'' ?>">Band che amo</a>
    <a href="/dashboard_links.php" class="<?= $activeTab==='links'?'active':'' ?>">Linktree</a>
    <a href="/dashboard_audio.php" class="<?= $activeTab==='audio'?'active':'' ?>">Brani</a>
    <?php if ($isBandOrLabel): ?>
    <a href="/dashboard_events.php" class="<?= $activeTab==='events'?'active':'' ?>">Eventi</a>
    <?php endif; ?>
    <a href="/dashboard_blog.php" class="<?= $activeTab==='blog'?'active':'' ?>">Blog</a>
    <a href="/dashboard_followers.php" class="<?= $activeTab==='followers'?'active':'' ?>">Follower</a>
  </div>
