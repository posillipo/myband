<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$admin = requireAdmin();
$activeAdminTab = 'tracking';
$pageTitle = 'Tracking';
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    setSiteSetting('gtm_head_script', $_POST['gtm_head_script'] ?? '');
    setSiteSetting('gtm_body_script', $_POST['gtm_body_script'] ?? '');
    setSiteSetting('fb_pixel_script', $_POST['fb_pixel_script'] ?? '');
    $success = 'Script di tracking aggiornati. Saranno visibili su tutte le pagine pubbliche entro pochi secondi.';
}

$gtmHead = getSiteSetting('gtm_head_script') ?: '';
$gtmBody = getSiteSetting('gtm_body_script') ?: '';
$fbPixel = getSiteSetting('fb_pixel_script') ?: '';

include __DIR__ . '/_admin_header.php';
?>
  <?php if ($success): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

  <div class="card">
    <strong>Come funziona</strong>
    <p style="color:var(--text-muted)">
      Incolla qui gli script forniti da Google Tag Manager e/o Facebook (Meta) Pixel. Vengono
      inseriti automaticamente su tutte le pagine pubbliche (homepage, pagine artista, blog,
      brani, eventi, contatti) — non serve modificare il codice.
    </p>
  </div>

  <form method="post" class="card">
    <?= csrfField() ?>

    <label>Google Tag Manager — script per l'&lt;head&gt;</label>
    <textarea name="gtm_head_script" rows="5" placeholder="&lt;script&gt;(function(w,d,s,l,i){...})(window,document,'script','dataLayer','GTM-XXXXXXX');&lt;/script&gt;"><?= e($gtmHead) ?></textarea>

    <label>Google Tag Manager — snippet noscript per subito dopo &lt;body&gt;</label>
    <textarea name="gtm_body_script" rows="4" placeholder="&lt;noscript&gt;&lt;iframe src=&quot;https://www.googletagmanager.com/ns.html?id=GTM-XXXXXXX&quot; ...&gt;&lt;/iframe&gt;&lt;/noscript&gt;"><?= e($gtmBody) ?></textarea>

    <label>Facebook (Meta) Pixel — script completo</label>
    <textarea name="fb_pixel_script" rows="6" placeholder="&lt;script&gt;!function(f,b,e,v,n,t,s){...}(window, document,'script', 'https://connect.facebook.net/en_US/fbevents.js');&lt;/script&gt;"><?= e($fbPixel) ?></textarea>

    <button type="submit" class="btn">Salva script di tracking</button>
  </form>
<?php include __DIR__ . '/_admin_footer.php'; ?>
