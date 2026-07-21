<?php
// Incluso da tutte le pagine dashboard_*. Richiede $user già caricato e $activeTab impostato.
// Nota: il tema è sempre "chiaro" per scelta di prodotto attuale. La colonna dashboard_theme
// resta nel database per un'eventuale reintroduzione futura della scelta, ma non viene più
// letta qui.
$dashTheme = 'light-theme';
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
  <div class="brand"><a href="/">myband<span>.it</span></a></div>
  <nav>
    <a href="/<?= e($user['slug']) ?>" target="_blank">Vedi pagina pubblica ↗</a>
    <?php if (!empty($user['is_admin'])): ?>
      <a href="/admin_dashboard.php">Area Admin</a>
    <?php endif; ?>
    <a href="/logout.php">Esci</a>
  </nav>
</div>
<div class="container">
  <?php $isBandOrLabel = in_array($user['account_type'] ?? 'band', ['band', 'label'], true); ?>
  <div class="tabs">
    <a href="/dashboard_timeline.php" class="<?= $activeTab==='timeline'?'active':'' ?>">La mia Timeline</a>
    <a href="/dashboard_post.php" class="<?= $activeTab==='post'?'active':'' ?>">Pubblica</a>
    <a href="/dashboard_profile.php" class="<?= $activeTab==='profile'?'active':'' ?>">Profilo</a>
    <a href="/dashboard_fan_bands.php" class="<?= $activeTab==='fan_bands'?'active':'' ?>">Band che amo</a>
    <a href="/dashboard_links.php" class="<?= $activeTab==='links'?'active':'' ?>">Link</a>
    <a href="/dashboard_audio.php" class="<?= $activeTab==='audio'?'active':'' ?>">Brani</a>
    <?php if ($isBandOrLabel): ?>
    <a href="/dashboard_events.php" class="<?= $activeTab==='events'?'active':'' ?>">Eventi</a>
    <?php endif; ?>
    <a href="/dashboard_blog.php" class="<?= $activeTab==='blog'?'active':'' ?>">Blog</a>
    <a href="/dashboard_contacts.php" class="<?= $activeTab==='contacts'?'active':'' ?>">Contatti</a>
    <a href="/dashboard_followers.php" class="<?= $activeTab==='followers'?'active':'' ?>">Follower</a>
    <?php if ($isBandOrLabel): ?>
    <a href="/dashboard_spotify.php" class="<?= $activeTab==='spotify'?'active':'' ?>">
      Spotify<?php if (!empty($user['spotify_artist_id'])): ?><span title="Profilo collegato" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1DB954;margin-left:6px;"></span><?php endif; ?>
    </a>
    <a href="/dashboard_podcast.php" class="<?= $activeTab==='podcast'?'active':'' ?>">
      Podcast<?php if (!empty($user['spotify_show_id'])): ?><span title="Podcast collegato" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1DB954;margin-left:6px;"></span><?php endif; ?>
    </a>
    <a href="/dashboard_youtube.php" class="<?= $activeTab==='youtube'?'active':'' ?>">
      YouTube<?php if (!empty($user['youtube_channel_id'])): ?><span title="Canale collegato" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#1DB954;margin-left:6px;"></span><?php endif; ?>
    </a>
    <?php endif; ?>
  </div>
