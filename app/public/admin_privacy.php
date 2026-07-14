<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$admin = requireAdmin();
$activeAdminTab = 'privacy';
$pageTitle = 'Privacy / Cookie';
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $script = $_POST['privacy_script'] ?? '';
    setSiteSetting('privacy_script', $script);
    $success = 'Script privacy aggiornato. Sarà visibile su tutte le pagine pubbliche entro pochi secondi.';
}

$currentScript = getSiteSetting('privacy_script') ?: '';

include __DIR__ . '/_admin_header.php';
?>
  <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

  <div class="card">
    <strong>Come funziona</strong>
    <p style="color:var(--text-muted)">
      Incolla qui sotto lo script di embed fornito dal tuo servizio di gestione privacy/cookie
      (es. <a href="https://www.iubenda.com/" target="_blank">Iubenda</a>, Cookiebot, ecc.).
      Lo script viene inserito automaticamente nell'<code>&lt;head&gt;</code> di ogni pagina pubblica
      del sito (homepage, pagine artista, blog, contatti) — non serve modificare il codice.
    </p>
  </div>

  <form method="post" class="card">
    <?= csrfField() ?>
    <label>Script privacy / cookie (HTML/JS fornito dal servizio esterno)</label>
    <textarea name="privacy_script" rows="10" placeholder="&lt;script type=&quot;text/javascript&quot;&gt;...&lt;/script&gt;"><?= e($currentScript) ?></textarea>
    <button type="submit" class="btn">Salva script</button>
  </form>

  <?php if (trim($currentScript) === ''): ?>
    <div class="alert error">Nessuno script privacy configurato al momento — le pagine pubbliche non mostrano alcun banner cookie.</div>
  <?php else: ?>
    <div class="alert success">Script attivo su tutte le pagine pubbliche.</div>
  <?php endif; ?>
<?php include __DIR__ . '/_admin_footer.php'; ?>
