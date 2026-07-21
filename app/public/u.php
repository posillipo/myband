<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = $_GET['slug'] ?? '';
$stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_show_id, p.genere, p.youtube_channel_id
                          FROM users u JOIN profiles p ON p.user_id = u.id
                          WHERE u.slug = ? AND u.is_active = 1');
$stmt->execute([$slug]);
$artist = $stmt->fetch();

if (!$artist) {
    http_response_code(404);
    echo '<h1 style="font-family:sans-serif;text-align:center;margin-top:80px;">Pagina non trovata</h1>';
    exit;
}

$uid = $artist['id'];

$links = getDB()->prepare('SELECT * FROM links WHERE user_id=? AND is_active=1 ORDER BY sort_order ASC, id ASC');
$links->execute([$uid]);
$links = $links->fetchAll();

$pageUrl = siteUrl('/' . $slug);
$ogImage = $artist['avatar_path'] ? siteUrl($artist['avatar_path']) : null;
$ogDescription = $artist['bio'] ? textExcerpt($artist['bio']) : ('La pagina di ' . $artist['display_name'] . ' su myband.it');

// Separiamo i link riconosciuti come social (icona tonda in alto, una sola per piattaforma)
// dagli altri (pulsante colorato grande) — include eventuali social ripetuti
[$socialLinks, $actionLinks] = splitSocialAndActionLinks($links);

$followerCount = getFollowerCount($uid);
$followMsg = $_GET['follow_msg'] ?? '';
$followErr = ($_GET['follow_err'] ?? '0') === '1';

$fanFavorites = [];
$stmt = getDB()->prepare('SELECT * FROM fan_favorite_bands WHERE user_id=? ORDER BY sort_order ASC');
$stmt->execute([$uid]);
$fanFavorites = $stmt->fetchAll();
$fanFavoritesTotal = count($fanFavorites);
$fanFavoritesPreview = array_slice($fanFavorites, 0, 6);
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($artist['display_name']) ?> — myband.it</title>
<meta name="description" content="<?= e($ogDescription) ?>">

<!-- Open Graph / condivisione social -->
<meta property="og:type" content="profile">
<meta property="og:title" content="<?= e($artist['display_name']) ?>">
<meta property="og:description" content="<?= e($ogDescription) ?>">
<meta property="og:url" content="<?= e($pageUrl) ?>">
<meta property="og:site_name" content="myband.it">
<?php if ($ogImage): ?><meta property="og:image" content="<?= e($ogImage) ?>"><?php endif; ?>

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= e($artist['display_name']) ?>">
<meta name="twitter:description" content="<?= e($ogDescription) ?>">

<link rel="canonical" href="<?= e($pageUrl) ?>">
<link rel="alternate" type="application/rss+xml" title="<?= e($artist['display_name']) ?> — myband.it" href="<?= e(siteUrl('/' . $slug . '/feed')) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<style>
  :root {
    --accent: <?= e($artist['theme_color'] ?: '#6C5CE7') ?>;
    --cf-1: #FFD6A5; --cf-2: #A0C4FF; --cf-3: #BDB2FF;
  }
</style>
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body class="colorful-page">
<?= embedTrackingBodyStart() ?>
<div class="container">
  <?= publicProfileHeader($artist, 'home', true) ?>

  <?php if ($followMsg): ?>
    <div class="alert <?= $followErr ? 'error' : 'success' ?>"><?= e($followMsg) ?></div>
  <?php endif; ?>

  <div class="card" style="text-align:center;">
    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$uid): ?>
      <?php $alreadyFollowing = isFollowingAccount((int)$_SESSION['user_id'], (int)$uid); ?>
      <form method="post" action="/follow_account.php">
        <?= csrfField() ?>
        <input type="hidden" name="user_id" value="<?= (int)$uid ?>">
        <input type="hidden" name="action" value="<?= $alreadyFollowing ? 'unfollow' : 'follow' ?>">
        <input type="hidden" name="redirect" value="/<?= e($slug) ?>">
        <button type="submit" class="btn" style="background:rgb(108,92,231);">
          <?= $alreadyFollowing ? 'Segui già ✓ (clicca per smettere)' : 'Segui ' . e($artist['display_name']) ?>
        </button>
      </form>
      <p style="color:rgba(34,34,59,0.7);font-size:13px;margin-top:10px;margin-bottom:0;">
        <?= getAccountFollowerCount((int)$uid) ?> persone ti seguono su myBand
      </p>
    <?php else: ?>
      <form method="post" action="/follow.php" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;align-items:center;">
        <?= csrfField() ?>
        <input type="hidden" name="slug" value="<?= e($slug) ?>">
        <input type="email" name="email" placeholder="La tua email" required style="flex:1;min-width:180px;max-width:280px;margin-bottom:0;">
        <button type="submit" class="btn" style="background:rgb(108,92,231);">Segui <?= e($artist['display_name']) ?></button>
      </form>
      <p style="color:rgba(34,34,59,0.7);font-size:13px;margin-top:10px;margin-bottom:0;">
        <?php if ($followerCount > 0): ?>
          <?= $followerCount ?> <?= $followerCount === 1 ? 'persona segue' : 'persone seguono' ?> questo artista
        <?php else: ?>
          Ricevi una notifica quando pubblica novità
        <?php endif; ?>
      </p>
    <?php endif; ?>
  </div>

  <?php if ($socialLinks): ?>
    <div class="social-icons-row">
      <?php foreach ($socialLinks as $l): ?>
        <a class="social-icon-btn" title="<?= e($l['platform']['label']) ?>" target="_blank" rel="noopener"
           href="/link.php?id=<?= (int)$l['id'] ?>"><i class="<?= e($l['platform']['icon_class']) ?>"></i></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if ($fanFavorites): ?>
    <div class="section-title" style="text-align:center;color:rgba(34,34,59,0.6);margin:18px 0 10px;">Band che amo</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:10px;">
      <?php foreach ($fanFavoritesPreview as $f): ?>
        <a href="https://open.spotify.com/artist/<?= e($f['spotify_artist_id']) ?>" target="_blank" rel="noopener"
           class="card" style="text-align:center;text-decoration:none;color:inherit;padding:14px 8px;">
          <?php if ($f['artist_image']): ?>
            <img src="<?= e($f['artist_image']) ?>" style="width:64px;height:64px;border-radius:50%;object-fit:cover;margin-bottom:8px;">
          <?php endif; ?>
          <div style="font-weight:700;font-size:13px;"><?= e($f['spotify_artist_name']) ?></div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($fanFavoritesTotal > 6): ?>
      <p style="text-align:center;margin-bottom:18px;">
        <a href="/<?= e($slug) ?>/band-che-amo">Vedi tutte (<?= $fanFavoritesTotal ?>) →</a>
      </p>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($actionLinks): ?>
    <?php foreach ($actionLinks as $i => $l): ?>
      <a class="color-link-btn" style="background:<?= e(COLORFUL_PALETTE[$i % count(COLORFUL_PALETTE)]) ?>;"
         target="_blank" rel="noopener"
         href="/link.php?id=<?= (int)$l['id'] ?>">
        <?php if ($l['cover_path']): ?>
          <img src="/<?= e($l['cover_path']) ?>" class="btn-cover-icon">
        <?php endif; ?>
        <?= e($l['label']) ?>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?= renderFloatingButtons() ?>
<?= renderSiteFooterBar($slug) ?>
</body>
</html>
