<?php
session_start();
require_once __DIR__ . '/../src/functions.php';
$admin = requireAdmin();
$activeAdminTab = 'import_legacy';
$pageTitle = 'Import dati legacy';

const CSV_PATH = '/var/www/import/legacy_import_ready.csv';

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'run_import') {
    checkCsrf();

    if (!is_readable(CSV_PATH)) {
        $result = ['error' => 'File CSV non trovato in ' . CSV_PATH . ' — verifica che il redeploy sia andato a buon fine.'];
    } else {
        $db = getDB();
        $handle = fopen(CSV_PATH, 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $skippedAlready = 0;
        $skippedEmailTaken = 0;
        $skippedSlugTaken = 0;
        $skippedBadRow = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                $skippedBadRow++;
                continue;
            }
            $data = array_combine($header, $row);

            $legacyGestoreId = (int) $data['legacy_gestore_id'];

            // Idempotenza: se questo gestore è già stato importato in un run precedente, salta
            $stmt = $db->prepare('SELECT id FROM users WHERE legacy_gestore_id = ?');
            $stmt->execute([$legacyGestoreId]);
            if ($stmt->fetch()) {
                $skippedAlready++;
                continue;
            }

            $email = strtolower(trim($data['email']));
            $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $skippedEmailTaken++;
                continue;
            }

            $slug = trim($data['slug']);
            $stmt = $db->prepare('SELECT id FROM users WHERE slug = ?');
            $stmt->execute([$slug]);
            if ($stmt->fetch()) {
                // Lo slug (derivato dal nome band) collide con un account già esistente (reale
                // o già importato prima con testi diversi): rendiamolo univoco aggiungendo l'ID
                // legacy, piuttosto che scartare il record
                $slug = $slug . '-' . $legacyGestoreId;
            }

            try {
                $db->beginTransaction();

                // Password inutilizzabile: l'account resta bloccato (is_active=0) finché non
                // sarà eventualmente attivato tramite un flusso dedicato futuro
                $randomPassword = password_hash(bin2hex(random_bytes(32)), PASSWORD_BCRYPT);

                $createdAt = null;
                if (!empty($data['created_at'])) {
                    $ts = strtotime($data['created_at']);
                    if ($ts) {
                        $createdAt = date('Y-m-d H:i:s', $ts);
                    }
                }

                $stmt = $db->prepare('INSERT INTO users
                    (slug, email, password_hash, is_active, is_admin, email_verified, legacy_gestore_id, legacy_band_id, legacy_stato, created_at)
                    VALUES (?, ?, ?, 0, 0, 0, ?, ?, ?, ?)');
                $stmt->execute([
                    $slug,
                    $email,
                    $randomPassword,
                    $legacyGestoreId,
                    (int) $data['legacy_band_id'],
                    $data['legacy_stato'] ?: null,
                    $createdAt ?: date('Y-m-d H:i:s'),
                ]);
                $userId = (int) $db->lastInsertId();

                $stmt = $db->prepare('INSERT INTO profiles
                    (user_id, display_name, bio, genere, citta, provincia, telefono)
                    VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([
                    $userId,
                    $data['display_name'],
                    $data['bio'] ?: null,
                    $data['genere'] ?: null,
                    $data['citta'] ?: null,
                    $data['provincia'] ?: null,
                    $data['telefono'] ?: null,
                ]);

                // Il sito ufficiale del vecchio profilo diventa un link nella nuova pagina pubblica
                if (!empty($data['sito_ufficiale']) && filter_var($data['sito_ufficiale'], FILTER_VALIDATE_URL)) {
                    $stmt = $db->prepare('INSERT INTO links (user_id, label, url, sort_order) VALUES (?, ?, ?, 1)');
                    $stmt->execute([$userId, 'Sito ufficiale', $data['sito_ufficiale']]);
                }

                $db->commit();
                $imported++;
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = "Gestore {$legacyGestoreId}: " . $e->getMessage();
            }
        }
        fclose($handle);

        $result = [
            'imported' => $imported,
            'skippedAlready' => $skippedAlready,
            'skippedEmailTaken' => $skippedEmailTaken,
            'skippedSlugTaken' => $skippedSlugTaken,
            'skippedBadRow' => $skippedBadRow,
            'errors' => $errors,
        ];
    }
}

// Statistiche correnti (utile anche solo per vedere se l'import è già stato fatto)
$stmt = getDB()->query('SELECT COUNT(*) c FROM users WHERE legacy_gestore_id IS NOT NULL');
$alreadyImported = (int) $stmt->fetch()['c'];

include __DIR__ . '/_admin_header.php';
?>
  <div class="card">
    <strong>Cosa fa questa importazione</strong>
    <p style="color:var(--text-muted)">
      Importa i profili band dal vecchio database (1.835 record puliti e pronti, uno per
      gestore, il primo in caso di gestori con più band). Ogni account viene creato con
      <strong>is_active = 0</strong> (non visibile pubblicamente, non può fare login) finché non
      deciderai come attivarli. È sicuro rilanciare questa pagina più volte: i gestori già
      importati vengono riconosciuti e saltati automaticamente.
    </p>
  </div>

  <div class="card">
    <strong>Già importati finora:</strong> <?= $alreadyImported ?> account
  </div>

  <?php if ($result): ?>
    <?php if (isset($result['error'])): ?>
      <div class="alert error"><?= e($result['error']) ?></div>
    <?php else: ?>
      <div class="alert success">
        Importazione completata:
        <strong><?= $result['imported'] ?></strong> nuovi account creati.
      </div>
      <div class="card">
        <strong>Dettaglio</strong>
        <p style="color:var(--text-muted)">
          Saltati perché già importati in precedenza: <?= $result['skippedAlready'] ?><br>
          Saltati per email già in uso da un account esistente: <?= $result['skippedEmailTaken'] ?><br>
          Righe malformate nel CSV: <?= $result['skippedBadRow'] ?><br>
          Errori durante l'inserimento: <?= count($result['errors']) ?>
        </p>
        <?php if ($result['errors']): ?>
          <details>
            <summary>Vedi dettaglio errori</summary>
            <pre style="white-space:pre-wrap;font-size:12px;"><?= e(implode("\n", array_slice($result['errors'], 0, 50))) ?></pre>
          </details>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <form method="post" onsubmit="return confirm('Avviare l\'importazione dei dati legacy? È un\'operazione sicura da ripetere, ma leggi bene cosa fa prima di procedere.');">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="run_import">
    <button type="submit" class="btn">Esegui importazione</button>
  </form>
<?php include __DIR__ . '/_admin_footer.php'; ?>
