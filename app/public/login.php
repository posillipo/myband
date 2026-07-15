<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$error = null;
$unverified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getDB()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($password, $u['password_hash'])) {
        $error = 'Email o password non corretti.';
    } elseif (!$u['is_active']) {
        $error = 'Account disattivato.';
    } elseif (!$u['email_verified']) {
        $unverified = true;
    } else {
        $_SESSION['user_id'] = $u['id'];
        if (!empty($_POST['remember'])) {
            issueRememberToken((int) $u['id']);
        }
        header('Location: /dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Accedi — myband.it</title>
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
<?= embedGoogleAnalytics() ?>
</head>
<body>
<?= embedTrackingBodyStart() ?>
<div class="navbar">
  <div class="brand"><a href="/">myband<span>.it</span></a></div>
  <nav><a href="/register.php">Registrati</a></nav>
</div>
<div class="container">
  <h2>Accedi</h2>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($unverified): ?>
    <div class="alert error">
      Devi prima confermare la tua email. Controlla la posta, oppure
      <a href="/resend_verification.php">richiedi un nuovo invio</a>.
    </div>
  <?php endif; ?>
  <form method="post" class="card">
    <?= csrfField() ?>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <label style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
      <input type="checkbox" name="remember" value="1" style="width:auto;">
      Ricordami su questo dispositivo (resta connesso per 30 giorni)
    </label>
    <button type="submit" class="btn">Accedi</button>
  </form>
</div>
</body>
</html>
