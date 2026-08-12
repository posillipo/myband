<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$user = requireLogin();
$activeTab = 'theme';
$pageTitle = 'Tema grafico';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();
    $theme = $_POST['page_theme'] ?? 'colorful';
    if (array_key_exists($theme, PAGE_THEMES)) {
        $stmt = getDB()->prepare('UPDATE profiles SET page_theme = ? WHERE user_id = ?');
        $stmt->execute([$theme, $user['id']]);
        $success = 'Tema aggiornato.';
        $user = currentUser();
    }
}

include __DIR__ . '/_dash_header.php';
?>
  <details class="help-box">
    <summary>ℹ️ Come funziona</summary>
    <p style="color:var(--text-muted)">
      Scegli l'aspetto grafico della tua pagina pubblica — il colore che hai scelto in
      "Profilo" resta il tuo accento personale in ogni tema, cambia solo lo stile generale
      (sfondo, forme, atmosfera).
    </p>
  </details>

  <?php if (!empty($success)): ?><div class="alert success"><?= e($success) ?></div><?php endif; ?>

  <form method="post">
    <?= csrfField() ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
      <?php foreach (PAGE_THEMES as $key => $theme): ?>
        <?php $isSelected = ($user['page_theme'] ?? 'colorful') === $key; ?>
        <label style="display:block;cursor:pointer;">
          <input type="radio" name="page_theme" value="<?= e($key) ?>" <?= $isSelected ? 'checked' : '' ?> onchange="this.form.submit()" style="display:none;">
          <div class="card" style="border:2px solid <?= $isSelected ? 'var(--accent)' : 'transparent' ?>;text-align:center;">
            <?php if ($key === 'wave'): ?>
              <div style="background:linear-gradient(160deg,#0b0b12,#1a1a2e);border-radius:6px;padding:16px 10px;margin-bottom:10px;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background:repeating-linear-gradient(115deg, rgba(108,92,231,0.25) 0 2px, transparent 2px 14px);"></div>
                <div style="position:relative;width:40px;height:40px;border-radius:50%;background:#222;margin:0 auto 8px;border:2px solid #6C5CE7;"></div>
                <div style="position:relative;background:rgba(255,255,255,0.1);border-radius:6px;height:10px;margin-bottom:4px;"></div>
                <div style="position:relative;background:rgba(255,255,255,0.1);border-radius:6px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'wave-light'): ?>
              <div style="background:linear-gradient(160deg,#f3f2f8,#e2e2ee);border-radius:6px;padding:16px 10px;margin-bottom:10px;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background:repeating-linear-gradient(115deg, rgba(34,34,59,0.08) 0 3px, transparent 3px 20px);"></div>
                <div style="position:relative;width:40px;height:40px;border-radius:50%;background:#fff;margin:0 auto 8px;border:2px solid #6C5CE7;"></div>
                <div style="position:relative;background:rgba(34,34,59,0.15);border-radius:6px;height:10px;margin-bottom:4px;"></div>
                <div style="position:relative;background:rgba(34,34,59,0.15);border-radius:6px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'aurora'): ?>
              <div style="background:linear-gradient(180deg,#131b2e 0%,#4a3157 45%,#d97a52 100%);border-radius:6px;padding:16px 10px;margin-bottom:10px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#222;margin:0 auto 8px;border:2px solid #fff;"></div>
                <div style="background:#e0654f;border-radius:8px;height:10px;margin-bottom:4px;box-shadow:0 2px 0 rgba(0,0,0,0.2);"></div>
                <div style="background:#e0654f;border-radius:8px;height:10px;box-shadow:0 2px 0 rgba(0,0,0,0.2);"></div>
              </div>
            <?php elseif ($key === 'plasma'): ?>
              <div style="background:linear-gradient(160deg,#7b2ff7,#ff2fb0,#2f6bff);border-radius:6px;padding:16px 10px;margin-bottom:10px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#fff;margin:0 auto 8px;"></div>
                <div style="background:#ff8a3d;border-radius:999px;height:10px;margin-bottom:4px;"></div>
                <div style="background:#ff8a3d;border-radius:999px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'golden'): ?>
              <div style="background:linear-gradient(180deg,#f3a35a,#8a4a3a,#2b1a1f);border-radius:6px;padding:16px 10px;margin-bottom:10px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#fff;margin:0 auto 8px;"></div>
                <div style="background:#fff;border-radius:999px;height:10px;margin-bottom:4px;"></div>
                <div style="background:#fff;border-radius:999px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'wave-neon'): ?>
              <div style="background:linear-gradient(160deg,#060609,#12121c);border-radius:6px;padding:16px 10px;margin-bottom:10px;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background:repeating-linear-gradient(90deg, rgba(108,92,231,0.3) 0 2px, transparent 2px 8px);"></div>
                <div style="position:relative;width:40px;height:40px;border-radius:50%;background:#111;margin:0 auto 8px;border:2px solid #6C5CE7;"></div>
                <div style="position:relative;background:rgba(255,255,255,0.08);border-radius:6px;height:10px;margin-bottom:4px;"></div>
                <div style="position:relative;background:rgba(255,255,255,0.08);border-radius:6px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'rock'): ?>
              <div style="background:#131313;border-radius:6px;padding:16px 10px;margin-bottom:10px;">
                <div style="width:40px;height:40px;border-radius:6px;border:2px solid #6C5CE7;margin:0 auto 8px;background:#1a1a1a;"></div>
                <div style="background:#1c1c1c;border-left:3px solid #6C5CE7;border-radius:3px;height:10px;margin-bottom:4px;"></div>
                <div style="background:#1c1c1c;border-left:3px solid #6C5CE7;border-radius:3px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'colorful'): ?>
              <div style="background:linear-gradient(160deg,#FFD6A5,#A0C4FF,#BDB2FF);border-radius:6px;padding:16px 10px;margin-bottom:10px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#fff;margin:0 auto 8px;"></div>
                <div style="background:rgba(255,255,255,0.6);border-radius:999px;height:10px;margin-bottom:4px;"></div>
                <div style="background:rgba(255,255,255,0.6);border-radius:999px;height:10px;"></div>
              </div>
            <?php elseif ($key === 'distortion'): ?>
              <div style="background:linear-gradient(165deg,#12121e,#1b0f30,#24123e);border-radius:6px;padding:16px 10px;margin-bottom:10px;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background:repeating-radial-gradient(circle at 50% 35%, rgba(160,32,240,0.35) 0px, rgba(160,32,240,0.35) 1px, transparent 2px, transparent 10px);"></div>
                <div style="position:relative;width:40px;height:40px;border-radius:50%;background:#1a1a24;margin:0 auto 8px;border:2px solid #a020f0;box-shadow:0 0 8px rgba(160,32,240,0.7);"></div>
                <div style="position:relative;background:radial-gradient(circle,rgba(0,0,0,0.35) 1px,transparent 1.4px) 0 0/5px 5px, linear-gradient(155deg,#ff8f1f,#e64500);border-radius:14px/8px;height:11px;margin-bottom:4px;"></div>
                <div style="position:relative;background:radial-gradient(circle,rgba(0,0,0,0.35) 1px,transparent 1.4px) 0 0/5px 5px, linear-gradient(155deg,#ff8f1f,#e64500);border-radius:14px/8px;height:11px;"></div>
              </div>
            <?php endif; ?>
            <strong><?= e($theme['label']) ?></strong>
            <?php if ($isSelected): ?><span style="color:var(--accent);font-size:12px;font-weight:700;"> ✓ attivo</span><?php endif; ?>
            <p style="color:var(--text-muted);font-size:12.5px;margin:4px 0 0;"><?= e($theme['description']) ?></p>
          </div>
        </label>
      <?php endforeach; ?>
    </div>
  </form>
<?php include __DIR__ . '/_dash_footer.php'; ?>
