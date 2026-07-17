<?php
require_once __DIR__ . '/db.php';

function slugify(string $text): string {
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Aggiunge un parametro di versione basato sulla data di modifica del file (cache-busting),
// così quando aggiorniamo il CSS il browser scarica sempre la versione corretta invece di
// usare una copia vecchia in cache.
// Nota: il percorso è quello reale della document root DENTRO il container Docker
// (/var/www/html, impostato dal Dockerfile) — non il percorso relativo del repository, che è
// diverso (src/ e public/ vengono copiati in due cartelle separate, non una dentro l'altra).
function assetUrl(string $path): string {
    $file = '/var/www/html' . $path;
    $v = @filemtime($file);
    return $path . ($v ? ('?v=' . $v) : '');
}

function csrfToken(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function checkCsrf(): void {
    if (!isset($_POST['csrf']) || !isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        http_response_code(403);
        die('Richiesta non valida (CSRF).');
    }
}

function currentUser(): ?array {
    attemptRememberLogin();
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path, p.theme_color, p.spotify_artist_id, p.spotify_artist_name, p.spotify_show_id, p.spotify_show_name, p.youtube_channel_id, p.youtube_channel_name
                              FROM users u LEFT JOIN profiles p ON p.user_id = u.id
                              WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

// ===== Login persistente "ricordami" (cookie selector/validator) =====
// Il cookie contiene "selector:validator" in chiaro, ma nel database salviamo solo l'hash del
// validator (mai il valore in chiaro) — così anche un accesso in lettura al database non
// permette di impersonare l'utente senza conoscere il validator originale dal cookie.

function issueRememberToken(int $userId): void {
    $selector = bin2hex(random_bytes(12));
    $validator = bin2hex(random_bytes(32));
    $hash = hash('sha256', $validator);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

    $stmt = getDB()->prepare('INSERT INTO remember_tokens (user_id, selector, validator_hash, expires_at) VALUES (?,?,?,?)');
    $stmt->execute([$userId, $selector, $hash, $expiresAt]);

    setcookie('remember_me', $selector . ':' . $validator, [
        'expires' => strtotime('+30 days'),
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function clearRememberToken(): void {
    if (!empty($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me'], 2);
        if (isset($parts[0]) && $parts[0] !== '') {
            getDB()->prepare('DELETE FROM remember_tokens WHERE selector = ?')->execute([$parts[0]]);
        }
    }
    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
}

// Se non c'è una sessione attiva ma esiste un cookie "ricordami" valido, effettua il login
// automatico e ruota il token (il vecchio viene invalidato, se ne emette uno nuovo) — pratica
// standard per limitare i danni in caso di furto del cookie.
function attemptRememberLogin(): void {
    if (!empty($_SESSION['user_id']) || empty($_COOKIE['remember_me'])) {
        return;
    }
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) !== 2) {
        return;
    }
    [$selector, $validator] = $parts;

    $stmt = getDB()->prepare('SELECT * FROM remember_tokens WHERE selector = ? AND expires_at >= NOW()');
    $stmt->execute([$selector]);
    $row = $stmt->fetch();
    if (!$row || !hash_equals($row['validator_hash'], hash('sha256', $validator))) {
        return;
    }

    $stmt = getDB()->prepare('SELECT is_active FROM users WHERE id = ?');
    $stmt->execute([$row['user_id']]);
    $u = $stmt->fetch();
    if (!$u || !$u['is_active']) {
        return;
    }

    $_SESSION['user_id'] = (int) $row['user_id'];
    getDB()->prepare('DELETE FROM remember_tokens WHERE id = ?')->execute([$row['id']]);
    issueRememberToken((int) $row['user_id']);
}

function requireLogin(): array {
    $u = currentUser();
    if (!$u) {
        header('Location: /login.php');
        exit;
    }
    return $u;
}

function requireAdmin(): array {
    $u = requireLogin();
    if (empty($u['is_admin'])) {
        http_response_code(403);
        die('Accesso riservato all\'amministratore.');
    }
    return $u;
}

function getSiteSetting(string $key): ?string {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = getDB()->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $cache[$key] = $row ? $row['setting_value'] : null;
}

function setSiteSetting(string $key, string $value): void {
    $stmt = getDB()->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    $stmt->execute([$key, $value]);
}

// Restituisce lo script privacy/cookie (es. Iubenda) impostato dall'admin, pronto per essere
// stampato nell'<head> di ogni pagina pubblica. Contenuto fidato: inserito solo dall'amministratore.
function embedPrivacyScript(): string {
    return getSiteSetting('privacy_script') ?: '';
}

// Genera lo snippet standard di Google Analytics (gtag.js) a partire dal solo Measurement ID
// (es. G-XXXXXXXXXX), così l'admin non deve incollare script complessi a mano.
function embedGoogleAnalytics(): string {
    $id = trim(getSiteSetting('ga_measurement_id') ?: '');
    if ($id === '') {
        return '';
    }
    $safeIdAttr = e($id);
    $safeIdJs = json_encode($id);
    return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $safeIdAttr . '"></script>' . "\n"
         . '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}'
         . 'gtag("js",new Date());gtag("config",' . $safeIdJs . ');</script>';
}

// Riconosce la piattaforma social da un URL, tra quelle mostrate come icona in cima alla pagina
// pubblica. Ogni voce ha una "key" univoca usata per la deduplicazione (un solo link per
// piattaforma viene mostrato come icona) e una classe Font Awesome completa per l'icona.
function detectPlatform(string $url): ?array {
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $map = [
        'spotify.com'      => ['key' => 'spotify',    'icon_class' => 'fa-brands fa-spotify',    'label' => 'Spotify'],
        'music.apple.com'  => ['key' => 'apple_music','icon_class' => 'fa-brands fa-apple',      'label' => 'Apple Music'],
        'instagram.com'    => ['key' => 'instagram',  'icon_class' => 'fa-brands fa-instagram',  'label' => 'Instagram'],
        'facebook.com'     => ['key' => 'facebook',   'icon_class' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
        'fb.com'           => ['key' => 'facebook',   'icon_class' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
        'tiktok.com'       => ['key' => 'tiktok',     'icon_class' => 'fa-brands fa-tiktok',     'label' => 'TikTok'],
        'youtube.com'      => ['key' => 'youtube',    'icon_class' => 'fa-brands fa-youtube',    'label' => 'YouTube'],
        'youtu.be'         => ['key' => 'youtube',    'icon_class' => 'fa-brands fa-youtube',    'label' => 'YouTube'],
        'linkedin.com'     => ['key' => 'linkedin',   'icon_class' => 'fa-brands fa-linkedin-in','label' => 'LinkedIn'],
        'soundcloud.com'   => ['key' => 'soundcloud', 'icon_class' => 'fa-brands fa-soundcloud', 'label' => 'SoundCloud'],
        'whatsapp.com'     => ['key' => 'whatsapp',   'icon_class' => 'fa-brands fa-whatsapp',   'label' => 'WhatsApp'],
        'wa.me'            => ['key' => 'whatsapp',   'icon_class' => 'fa-brands fa-whatsapp',   'label' => 'WhatsApp'],
    ];
    foreach ($map as $domain => $info) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return $info;
        }
    }
    return null;
}

// Separa i link di un utente in: icone social (una sola per piattaforma, la PRIMA incontrata
// scorrendo l'elenco così come ordinato dal band manager nella propria dashboard) e pulsanti
// azione (tutto il resto: ripetizioni della stessa piattaforma, e link non riconosciuti).
// Un link marcato manualmente come "sito web personale" diventa sempre un'icona (globo),
// indipendentemente dal dominio, perché un sito personale non è riconoscibile automaticamente.
function splitSocialAndActionLinks(array $links): array {
    $socialLinks = [];
    $actionLinks = [];
    $seenKeys = [];
    foreach ($links as $l) {
        if (!empty($l['is_website_icon']) && !isset($seenKeys['website'])) {
            $socialLinks[] = $l + ['platform' => ['key' => 'website', 'icon_class' => 'fa-solid fa-globe', 'label' => 'Sito web']];
            $seenKeys['website'] = true;
            continue;
        }
        $platform = detectPlatform($l['url']);
        if ($platform && !isset($seenKeys[$platform['key']])) {
            $socialLinks[] = $l + ['platform' => $platform];
            $seenKeys[$platform['key']] = true;
        } else {
            $actionLinks[] = $l;
        }
    }
    return [$socialLinks, $actionLinks];
}

// Palette di colori pastello per i pulsanti "azione" nel tema colorato della pagina pubblica
const COLORFUL_PALETTE = ['#FFD6A5', '#FDFFB6', '#CAFFBF', '#9BF6FF', '#A0C4FF', '#BDB2FF', '#FFC6FF', '#FFADAD'];

// Menu di navigazione condiviso tra tutte le pagine pubbliche di un artista (Home | Blog | Brani | Eventi | Contatti)
// Il tab "Spotify" compare solo se l'artista ha collegato un profilo Spotify dalla dashboard.
function publicNav(string $slug, string $active, bool $hasSpotify = false, bool $hasYoutube = false, bool $hasPodcast = false): string {
    $tabs = [
        'home' => ['label' => 'Home', 'url' => '/' . $slug],
    ];
    if ($hasSpotify) {
        $tabs['spotify'] = ['label' => 'Spotify', 'url' => '/' . $slug . '/spotify'];
    }
    if ($hasPodcast) {
        $tabs['podcast'] = ['label' => 'Podcast', 'url' => '/' . $slug . '/podcast'];
    }
    if ($hasYoutube) {
        $tabs['video'] = ['label' => 'Video', 'url' => '/' . $slug . '/video'];
    }
    $tabs['blog'] = ['label' => 'Blog', 'url' => '/' . $slug . '/blog'];
    $tabs['brani'] = ['label' => 'Brani', 'url' => '/' . $slug . '/brani'];
    $tabs['eventi'] = ['label' => 'Eventi', 'url' => '/' . $slug . '/eventi'];
    $tabs['contatti'] = ['label' => 'Contatti', 'url' => '/' . $slug . '/contatti'];

    $parts = [];
    foreach ($tabs as $key => $t) {
        // La voce della pagina attiva è bianca, per distinguersi visivamente dalle altre
        $activeAttr = $key === $active ? ' style="font-weight:900;color:#fff;"' : '';
        $parts[] = '<a href="' . e($t['url']) . '"' . $activeAttr . '>' . e($t['label']) . '</a>';
    }
    return '<p class="colorful-nav" style="margin-top:10px;">' . implode('<span> | </span>', $parts) . '</p>';
}

// Blocco identità condiviso (avatar + nome + eventuale bio + menu) stampato in cima ad ogni
// pagina pubblica dell'artista (home, blog, brani, eventi, contatti, spotify), per un aspetto
// coerente. La bio, quando presente, è mostrata come vignetta al passaggio del mouse
// sull'avatar (non più come testo sempre visibile), per un profilo più compatto.
function publicProfileHeader(array $artist, string $active, bool $showBio = false): string {
    $html = '<div class="profile-header">';
    if (!empty($artist['avatar_path'])) {
        $html .= '<div class="avatar-wrap">';
        $html .= '<img class="avatar" src="/' . e($artist['avatar_path']) . '" alt="' . e($artist['display_name']) . '">';
        if ($showBio && !empty($artist['bio'])) {
            $html .= '<div class="avatar-bio-tooltip">' . nl2br(e($artist['bio'])) . '</div>';
        }
        $html .= '</div>';
    } elseif ($showBio && !empty($artist['bio'])) {
        // Senza avatar non c'è nulla su cui fare hover: la bio resta visibile come testo normale
        $html .= '<p>' . nl2br(e($artist['bio'])) . '</p>';
    }
    $html .= '<h1>' . e($artist['display_name']) . '</h1>';
    $html .= publicNav($artist['slug'], $active, !empty($artist['spotify_artist_id']), !empty($artist['youtube_channel_id']), !empty($artist['spotify_show_id']));
    $html .= '</div>';
    return $html;
}

// Barra fissa in fondo alla pagina che invita alla registrazione, presente su tutte le pagine
// pubbliche del sito.
// Blocco fisso in fondo a tutte le pagine pubbliche: link Cookie/Privacy/myBand + invito
// registrazione, sempre uniti insieme in un unico contenitore (mai più separati da uno spazio
// vuoto quando la pagina sopra ha poco contenuto). Se viene passato lo slug dell'artista, mostra
// anche un badge fluttuante "myband.it/slug" in stile Linktree, sopra il blocco.
function renderSiteFooterBar(string $slug = ''): string {
    $privacyUrl = getSiteSetting('privacy_policy_url') ?: '';
    $parts = [];
    // CookieYes intercetta automaticamente qualsiasi elemento con questa classe per riaprire
    // il pannello delle preferenze cookie — non serve nessuna chiamata JavaScript esplicita.
    $parts[] = '<a href="#" class="cky-banner-element">Preferenze Cookie</a>';
    if ($privacyUrl !== '') {
        $parts[] = '<a href="' . e($privacyUrl) . '" target="_blank" rel="noopener">Privacy</a>';
    } else {
        $parts[] = '<a href="/">Privacy</a>';
    }
    $parts[] = '<a href="/">myBand</a>';
    $linksRow = '<div class="footer-links">' . implode('<span> · </span>', $parts) . '</div>';
    $joinRow = '<a href="/register.php" class="join-bar">Unisciti a myBand</a>';
    $badge = $slug !== '' ? '<div class="short-link-badge">myband.it/' . e($slug) . '</div>' : '';
    return '<div class="site-footer-fixed">' . $badge . $linksRow . $joinRow . '</div>';
}

// Legge la configurazione SMTP: priorità alle impostazioni salvate dall'admin nel database,
// con ripiego sulle variabili d'ambiente (per compatibilità con configurazioni precedenti).
function getSmtpConfig(): array {
    $host = getSiteSetting('smtp_host');
    $host = ($host !== null && $host !== '') ? $host : (getenv('SMTP_HOST') ?: '');

    $port = getSiteSetting('smtp_port');
    $port = ($port !== null && $port !== '') ? (int) $port : (int) (getenv('SMTP_PORT') ?: 587);

    $user = getSiteSetting('smtp_user');
    $user = ($user !== null && $user !== '') ? $user : (getenv('SMTP_USER') ?: '');

    $pass = getSiteSetting('smtp_pass');
    $pass = ($pass !== null && $pass !== '') ? $pass : (getenv('SMTP_PASS') ?: '');

    $secure = getSiteSetting('smtp_secure');
    $secure = ($secure !== null && $secure !== '') ? $secure : (getenv('SMTP_SECURE') ?: 'tls');

    $from = getSiteSetting('smtp_from');
    $from = ($from !== null && $from !== '') ? $from : (getenv('SMTP_FROM') ?: $user);

    $fromName = getSiteSetting('smtp_from_name');
    $fromName = ($fromName !== null && $fromName !== '') ? $fromName : (getenv('SMTP_FROM_NAME') ?: 'myband.it');

    $verifyCertSetting = getSiteSetting('smtp_verify_cert');
    $verifyCert = ($verifyCertSetting === null || $verifyCertSetting === '') ? true : ($verifyCertSetting === '1');

    return compact('host', 'port', 'user', 'pass', 'secure', 'from', 'fromName', 'verifyCert');
}

// Invia una notifica email al musicista quando riceve un nuovo messaggio di contatto/booking.
// Se l'SMTP non è configurato (né da admin né da variabili d'ambiente), non fa nulla (nessun
// errore, la richiesta resta comunque salvata nel database e visibile in dashboard).
function notifyNewContact(string $toEmail, string $toName, string $senderName, string $senderEmail, string $message, string $publicUrl): void {
    $cfg = getSmtpConfig();
    if (!$cfg['host']) {
        return;
    }

    require_once __DIR__ . '/mailer.php';
    $mailer = new SimpleSmtpMailer($cfg['host'], $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['secure'], $cfg['verifyCert']);

    $subject = "Nuovo messaggio da {$senderName} su myband.it";
    $body = "Hai ricevuto un nuovo messaggio dalla tua pagina {$publicUrl}:\n\n"
          . "Nome: {$senderName}\n"
          . "Email: {$senderEmail}\n\n"
          . "Messaggio:\n{$message}\n\n"
          . "---\nRispondi direttamente a questa email per contattare {$senderName},\n"
          . "oppure gestisci tutti i messaggi dalla tua dashboard su myband.it.";

    $mailer->send($cfg['from'], $cfg['fromName'], $toEmail, $toName, $subject, $body);
}

// Genera un token di verifica email (valido 24 ore)
function generateVerificationToken(): array {
    return [bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+24 hours'))];
}

// Invia l'email di conferma registrazione con il link di verifica. Come per le notifiche di
// contatto: se l'SMTP non è configurato non fa nulla (nessun errore).
function notifyEmailVerification(string $toEmail, string $toName, string $token): bool {
    $cfg = getSmtpConfig();
    if (!$cfg['host']) {
        return false;
    }

    require_once __DIR__ . '/mailer.php';
    $mailer = new SimpleSmtpMailer($cfg['host'], $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['secure'], $cfg['verifyCert']);

    $link = siteUrl('/verify.php?token=' . $token);
    $subject = "Conferma il tuo account su myband.it";
    $body = "Ciao {$toName},\n\n"
          . "Grazie per esserti registrato su myband.it! Conferma il tuo account cliccando\n"
          . "questo link (valido per 24 ore):\n\n{$link}\n\n"
          . "Se non hai richiesto tu questa registrazione, ignora pure questa email.";

    return $mailer->send($cfg['from'], $cfg['fromName'], $toEmail, $toName, $subject, $body);
}

// Invia l'email con il link per reimpostare la password (valido 1 ora, più breve della verifica
// email perché un link di reset password è più sensibile). Come le altre notifiche: se l'SMTP
// non è configurato, non fa nulla (nessun errore).
function notifyPasswordReset(string $toEmail, string $toName, string $token): bool {
    $cfg = getSmtpConfig();
    if (!$cfg['host']) {
        return false;
    }

    require_once __DIR__ . '/mailer.php';
    $mailer = new SimpleSmtpMailer($cfg['host'], $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['secure'], $cfg['verifyCert']);

    $link = siteUrl('/reset_password.php?token=' . $token);
    $subject = "Reimposta la tua password su myband.it";
    $body = "Ciao {$toName},\n\n"
          . "Hai richiesto di reimpostare la password del tuo account myband.it. Clicca questo\n"
          . "link per scegliere una nuova password (valido 1 ora):\n\n{$link}\n\n"
          . "Se non hai richiesto tu il reset, ignora pure questa email: la tua password attuale\n"
          . "resta invariata.";

    return $mailer->send($cfg['from'], $cfg['fromName'], $toEmail, $toName, $subject, $body);
}
function embedTrackingHead(): string {
    $gtm = getSiteSetting('gtm_head_script') ?: '';
    $pixel = getSiteSetting('fb_pixel_script') ?: '';
    return $gtm . "\n" . $pixel;
}

function embedTrackingBodyStart(): string {
    return getSiteSetting('gtm_body_script') ?: '';
}

// Gestisce l'upload di un'immagine di copertina (link, articoli blog, eventi). Restituisce il
// percorso relativo salvato, o null se non è stato caricato nessun file valido. Non lancia mai
// errori: un file mancante o non valido significa semplicemente "nessuna copertina".
function handleCoverUpload(int $userId, string $fileInputName = 'cover'): ?string {
    if (empty($_FILES[$fileInputName]['name'])) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $fname = 'cover_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $dest = '/var/www/html/uploads/images/' . $fname;
    if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $dest)) {
        return 'uploads/images/' . $fname;
    }
    return null;
}

// Elimina il file di copertina dal disco, se presente (usato quando si elimina un link/post/evento)
function deleteCoverFile(?string $coverPath): void {
    if ($coverPath) {
        @unlink('/var/www/html/' . $coverPath);
    }
}

// ===== Sistema "Segui via email" =====

function getFollowerCount(int $artistUserId): int {
    $stmt = getDB()->prepare('SELECT COUNT(*) c FROM followers WHERE user_id = ? AND verified = 1');
    $stmt->execute([$artistUserId]);
    return (int) $stmt->fetch()['c'];
}

// Invia l'email di conferma iscrizione (doppio opt-in, anti-spam). Se l'SMTP non è
// configurato, non fa nulla (nessun errore).
function notifyFollowConfirmation(string $toEmail, string $artistName, string $token, string $confirmUrl): bool {
    $cfg = getSmtpConfig();
    if (!$cfg['host']) {
        return false;
    }
    require_once __DIR__ . '/mailer.php';
    $mailer = new SimpleSmtpMailer($cfg['host'], $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['secure'], $cfg['verifyCert']);

    $subject = "Conferma: segui {$artistName} su myband.it";
    $body = "Ciao,\n\n"
          . "Hai chiesto di seguire {$artistName} su myband.it. Conferma cliccando questo link:\n\n"
          . "{$confirmUrl}\n\n"
          . "Da quel momento riceverai un'email quando {$artistName} pubblica un nuovo articolo\n"
          . "o annuncia un nuovo concerto.\n\n"
          . "Se non hai richiesto tu questa iscrizione, ignora pure questa email: non verrà\n"
          . "attivata alcuna iscrizione senza la tua conferma.";

    return $mailer->send($cfg['from'], $cfg['fromName'], $toEmail, $toEmail, $subject, $body);
}

// Notifica tutti i follower verificati di un artista quando pubblica un nuovo contenuto
// (articolo blog o evento). "Best effort": eventuali errori di invio ai singoli indirizzi non
// bloccano gli altri né l'operazione che ha generato la notifica (pubblicare un post/evento
// resta valida anche se le email non partissero per qualche motivo).
function notifyFollowersNewContent(int $artistUserId, string $artistName, string $artistSlug, string $type, string $title, string $contentUrl): void {
    $cfg = getSmtpConfig();
    if (!$cfg['host']) {
        return;
    }
    $stmt = getDB()->prepare('SELECT email, token FROM followers WHERE user_id = ? AND verified = 1');
    $stmt->execute([$artistUserId]);
    $followers = $stmt->fetchAll();
    if (!$followers) {
        return;
    }

    require_once __DIR__ . '/mailer.php';
    $mailer = new SimpleSmtpMailer($cfg['host'], $cfg['port'], $cfg['user'], $cfg['pass'], $cfg['secure'], $cfg['verifyCert']);

    $label = $type === 'evento' ? 'un nuovo concerto' : 'un nuovo articolo';
    $subject = "{$artistName} ha pubblicato {$label} su myband.it";

    foreach ($followers as $f) {
        $unsubscribeUrl = siteUrl('/follow_unsubscribe.php?token=' . $f['token']);
        $body = "Ciao,\n\n"
              . "{$artistName} ha appena pubblicato {$label}:\n\n"
              . "\"{$title}\"\n\n"
              . "Vai a vederlo qui: {$contentUrl}\n\n"
              . "---\n"
              . "Ricevi questa email perché segui {$artistName} su myband.it.\n"
              . "Per non ricevere più queste notifiche: {$unsubscribeUrl}";

        $mailer->send($cfg['from'], $cfg['fromName'], $f['email'], $f['email'], $subject, $body);
    }
}

function slugExists(string $slug): bool {
    $stmt = getDB()->prepare('SELECT id FROM users WHERE slug = ?');
    $stmt->execute([$slug]);
    return (bool) $stmt->fetch();
}

// Whitelist di route dell'app: uno slug musicista non può collidere con queste pagine
const RESERVED_SLUGS = ['login','register','logout','dashboard','dashboard_profile',
    'dashboard_links','dashboard_audio','dashboard_events','dashboard_blog',
    'dashboard_contacts','u','index','assets','uploads','blog','contatti','link',
    'admin','admin_users','admin_user_detail','admin_privacy','brani','eventi',
    'verify','resend_verification','admin_dashboard','admin_user_edit','admin_contacts','admin_tracking','admin_smtp',
    'admin_spotify','dashboard_spotify','follow','follow_confirm','follow_unsubscribe','dashboard_followers',
    'admin_import_legacy','admin_profiles','track','evento','admin_youtube','dashboard_youtube','video',
    'forgot_password','reset_password','dashboard_podcast','podcast'];

// Genera uno slug univoco per un articolo di un dato utente (title -> slug, con suffisso -2, -3... se già esistente)
function generateUniquePostSlug(int $userId, string $title, ?int $excludePostId = null): string {
    $base = slugify($title) ?: 'articolo';
    $slug = $base;
    $i = 2;
    while (true) {
        $sql = 'SELECT id FROM blog_posts WHERE user_id = ? AND slug = ?';
        $params = [$userId, $slug];
        if ($excludePostId) {
            $sql .= ' AND id != ?';
            $params[] = $excludePostId;
        }
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $i;
        $i++;
    }
}

// Costruisce il permalink SEO di un articolo: /nomeutente/blog/anno.mese.giorno.slug-articolo
function blogPostUrl(string $userSlug, array $post): string {
    $datePart = date('Y.m.d', strtotime($post['published_at']));
    return '/' . $userSlug . '/blog/' . $datePart . '.' . $post['slug'];
}

// URL assoluta del sito (per meta tag Open Graph / condivisione social), usa SITE_URL se impostata
function siteUrl(string $path = ''): string {
    $base = rtrim(getenv('SITE_URL') ?: '', '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . '/' . ltrim($path, '/');
}

// Estratto in testo semplice per meta description / anteprima social
function textExcerpt(string $text, int $length = 160): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length - 1) . '…';
}
