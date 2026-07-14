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
    if (empty($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = getDB()->prepare('SELECT u.*, p.display_name, p.bio, p.avatar_path, p.theme_color
                              FROM users u LEFT JOIN profiles p ON p.user_id = u.id
                              WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
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

// Riconosce la piattaforma social da un URL (per mostrare un'iconcina invece del pulsante grande)
function detectPlatform(string $url): ?array {
    $host = strtolower((string) parse_url($url, PHP_URL_HOST));
    $map = [
        'spotify.com'      => ['icon' => '🎵', 'label' => 'Spotify'],
        'music.apple.com'  => ['icon' => '🍎', 'label' => 'Apple Music'],
        'instagram.com'    => ['icon' => '📸', 'label' => 'Instagram'],
        'tiktok.com'       => ['icon' => '🎬', 'label' => 'TikTok'],
        'youtube.com'      => ['icon' => '▶️', 'label' => 'YouTube'],
        'youtu.be'         => ['icon' => '▶️', 'label' => 'YouTube'],
        'twitter.com'      => ['icon' => '𝕏', 'label' => 'X'],
        'x.com'            => ['icon' => '𝕏', 'label' => 'X'],
        'threads.net'      => ['icon' => '@', 'label' => 'Threads'],
        'bsky.app'         => ['icon' => '🦋', 'label' => 'Bluesky'],
        'facebook.com'     => ['icon' => '👤', 'label' => 'Facebook'],
        'soundcloud.com'   => ['icon' => '☁️', 'label' => 'SoundCloud'],
        'linkedin.com'     => ['icon' => '💼', 'label' => 'LinkedIn'],
        'whatsapp.com'     => ['icon' => '💬', 'label' => 'WhatsApp'],
        'wa.me'            => ['icon' => '💬', 'label' => 'WhatsApp'],
    ];
    foreach ($map as $domain => $info) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return $info;
        }
    }
    return null;
}

// Palette di colori pastello per i pulsanti "azione" nel tema colorato della pagina pubblica
const COLORFUL_PALETTE = ['#FFD6A5', '#FDFFB6', '#CAFFBF', '#9BF6FF', '#A0C4FF', '#BDB2FF', '#FFC6FF', '#FFADAD'];

// Menu di navigazione condiviso tra tutte le pagine pubbliche di un artista (Home | Blog | Brani | Eventi | Contatti)
function publicNav(string $slug, string $active): string {
    $tabs = [
        'home'     => ['label' => 'Home', 'url' => '/' . $slug],
        'blog'     => ['label' => 'Blog', 'url' => '/' . $slug . '/blog'],
        'brani'    => ['label' => 'Brani', 'url' => '/' . $slug . '/brani'],
        'eventi'   => ['label' => 'Eventi', 'url' => '/' . $slug . '/eventi'],
        'contatti' => ['label' => 'Contatti', 'url' => '/' . $slug . '/contatti'],
    ];
    $parts = [];
    foreach ($tabs as $key => $t) {
        $activeAttr = $key === $active ? ' style="font-weight:900;text-decoration:underline;"' : '';
        $parts[] = '<a href="' . e($t['url']) . '"' . $activeAttr . '>' . e($t['label']) . '</a>';
    }
    return '<p class="colorful-nav" style="margin-top:10px;">' . implode('<span> | </span>', $parts) . '</p>';
}

// Blocco identità condiviso (avatar + nome + eventuale bio + menu) stampato in cima ad ogni
// pagina pubblica dell'artista (home, blog, brani, eventi, contatti), per un aspetto coerente
function publicProfileHeader(array $artist, string $active, bool $showBio = false): string {
    $html = '<div class="profile-header">';
    if (!empty($artist['avatar_path'])) {
        $html .= '<img class="avatar" src="/' . e($artist['avatar_path']) . '" alt="' . e($artist['display_name']) . '">';
    }
    $html .= '<h1>' . e($artist['display_name']) . '</h1>';
    if ($showBio && !empty($artist['bio'])) {
        $html .= '<p>' . nl2br(e($artist['bio'])) . '</p>';
    }
    $html .= publicNav($artist['slug'], $active);
    $html .= '</div>';
    return $html;
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
    'admin','admin_users','admin_user_detail','admin_privacy','brani','eventi'];

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
