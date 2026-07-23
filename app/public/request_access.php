<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

$error = null;
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bandName = trim($_POST['band_name'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Compila nome ed email con un indirizzo valido.';
    } else {
        $stmt = getDB()->prepare('INSERT INTO access_requests (name, email, band_name, message) VALUES (?,?,?,?)');
        $stmt->execute([$name, $email, $bandName ?: null, $message ?: null]);
        $sent = true;
    }
}
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Richiedi l'accesso — myband.it</title>
<link rel="stylesheet" href="<?= assetUrl('/assets/css/style.css') ?>">
<?= embedPrivacyScript() ?>
</head>
<body>
<div class="auth-split">
  <div class="auth-split-brand">
    <div class="logo">my<span>Band</span>.it</div>
    <h1>Richiedi il tuo <span class="highlight">posto</span><br>su myBand.</h1>
  </div>
  <div class="auth-split-form">
    <div class="auth-split-form-inner">
      <?php if ($sent): ?>
        <div class="alert success">
          Richiesta ricevuta! Ti scriveremo appena verrà valutata, con le istruzioni per
          completare la registrazione.
        </div>
        <p style="margin-top:18px;font-size:14px;"><a href="/">← Torna alla home</a></p>
      <?php else: ?>
        <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
        <p style="color:#444;font-size:14px;margin-bottom:20px;">
          myBand è ad accesso su invito: raccontaci chi sei, ti risponderemo appena possibile.
        </p>
        <form method="post">
          <?= csrfField() ?>
          <label>Il tuo nome</label>
          <input type="text" name="name" required>
          <label>Email</label>
          <input type="email" name="email" required>
          <label>Nome della band/progetto (opzionale)</label>
          <input type="text" name="band_name">
          <label>Raccontaci brevemente di te (opzionale)</label>
          <textarea name="message" rows="3"></textarea>
          <button type="submit" class="btn-dark">Invia richiesta</button>
        </form>
      <?php endif; ?>
      <p style="margin-top:18px;font-size:14px;">Hai già un invito? <a href="/login.php">Accedi</a></p>
    </div>
  </div>
</div>
</body>
</html>
