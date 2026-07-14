<?php
session_start();
require_once __DIR__ . '/../src/functions.php';

// Evita che il browser mostri una copia precaricata/in cache della pagina con un
// token CSRF non più abbinato alla sessione corrente (es. preload/prefetch di Edge/Chrome)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$error = null;
$registered = false;
$registeredEmailSent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $displayName = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $slug = slugify($_POST['slug'] ?? $displayName);

    if ($displayName === '' || $email === '' || $password === '' || $slug === '') {
        $error = 'Compila tutti i campi.';
    } elseif (strlen($password) < 8) {
        $error = 'La password deve avere almeno 8 caratteri.';
    } elseif (in_array($slug, RESERVED_SLUGS, true)) {
        $error = 'Questo nome pagina non è disponibile, scegline un altro.';
    } elseif (slugExists($slug)) {
        $error = 'Questo nome pagina è già in uso.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email già registrata.';
        } else {
            $db->beginTransaction();
            $hash = password_hash($password, PASSWORD_BCRYPT);
            [$token, $expires] = generateVerificationToken();
            $stmt = $db->prepare('INSERT INTO users (slug, email, password_hash, email_verified, verification_token, verification_expires) VALUES (?, ?, ?, 0, ?, ?)');
            $stmt->execute([$slug, $email, $hash, $token, $expires]);
            $userId = (int) $db->lastInsertId();
            $stmt = $db->prepare('INSERT INTO profiles (user_id, display_name) VALUES (?, ?)');
            $stmt->execute([$userId, $displayName]);
            $db->commit();

            $emailSent = notifyEmailVerification($email, $displayName, $token);
            $registered = true;
            $registeredEmailSent = $emailSent;
        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registrati — myband.it</title>
<link rel="stylesheet" href="/assets/css/style.css">
<?= embedPrivacyScript() ?>
<?= embedTrackingHead() ?>
</head>
<body>
<?= embedTrackingBodyStart() ?>
<div class="navbar">
  <div class="brand"><a href="/">myband<span>.it</span></a></div>
  <nav><a href="/login.php">Accedi</a></nav>
</div>
<div class="container">
  <h2>Crea la tua pagina</h2>
  <?php if ($registered): ?>
    <div class="alert success">
      <strong>Registrazione completata!</strong><br>
      <?php if ($registeredEmailSent): ?>
        Ti abbiamo inviato un'email di conferma: apri il link contenuto nel messaggio per
        attivare il tuo account (valido 24 ore). Dopo la conferma potrai accedere normalmente.
      <?php else: ?>
        Il tuo account è stato creato ma non è stato possibile inviare l'email di conferma
        automaticamente. Contatta l'amministratore per attivare il tuo account.
      <?php endif; ?>
    </div>
    <p><a href="/login.php">Vai al login</a></p>
  <?php else: ?>
  <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="card">
    <?= csrfField() ?>
    <label>Nome d'arte / Band</label>
    <input type="text" name="display_name" required value="<?= e($_POST['display_name'] ?? '') ?>">

    <label>Nome pagina (myband.it/<strong>nomepagina</strong>)</label>
    <input type="text" name="slug" placeholder="es. marco-rossi-band" value="<?= e($_POST['slug'] ?? '') ?>">

    <label>Email</label>
    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">

    <label>Password (min. 8 caratteri)</label>
    <input type="password" name="password" required>

    <button type="submit" class="btn">Crea pagina</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
