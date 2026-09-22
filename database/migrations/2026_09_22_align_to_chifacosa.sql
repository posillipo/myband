-- Script rigenerato confrontando DIRETTAMENTE la struttura reale di produzione
-- (information_schema + SHOW CREATE TABLE) con lo schema.sql di chifacosa,
-- tabella per tabella, colonna per colonna. Sostituisce le versioni precedenti
-- basate sulla cronologia delle migrazioni (rivelatasi incompleta).

-- ============================================================
-- PARTE 1: colonne da aggiungere a tabelle GIA' esistenti
-- ============================================================
ALTER TABLE users ADD COLUMN otp_attempts INT NOT NULL DEFAULT 0;
ALTER TABLE profiles ADD COLUMN custom_feed_guid VARCHAR(500) DEFAULT NULL;
ALTER TABLE profiles ADD COLUMN custom_feed_guid_since DATETIME DEFAULT NULL;
ALTER TABLE profiles ADD COLUMN cinema_films_json_url VARCHAR(500) DEFAULT NULL;
ALTER TABLE profiles ADD COLUMN cinema_films_synced_at DATETIME DEFAULT NULL;
ALTER TABLE profiles ADD COLUMN cinema_ticket_price DECIMAL(6,2) DEFAULT NULL;
ALTER TABLE profiles ADD COLUMN menu_preconto_enabled TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE links ADD COLUMN link_type ENUM('link','divider','map','film') NOT NULL DEFAULT 'link';
ALTER TABLE links ADD COLUMN map_lat DECIMAL(10,7) DEFAULT NULL;
ALTER TABLE links ADD COLUMN map_lng DECIMAL(10,7) DEFAULT NULL;
ALTER TABLE links ADD COLUMN external_ref VARCHAR(64) DEFAULT NULL;
ALTER TABLE links ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE events ADD COLUMN description TEXT DEFAULT NULL;
ALTER TABLE events ADD COLUMN is_perpetual TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE events ADD COLUMN recurrence ENUM('none','weekdays','weekend') NOT NULL DEFAULT 'none';
ALTER TABLE events ADD COLUMN accepts_reservations TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE blog_posts ADD COLUMN album_id INT DEFAULT NULL;
ALTER TABLE blog_posts ADD COLUMN tags VARCHAR(300) DEFAULT NULL;
ALTER TABLE followers ADD COLUMN first_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE followers ADD COLUMN last_name VARCHAR(100) DEFAULT NULL;
ALTER TABLE followers ADD COLUMN phone VARCHAR(30) DEFAULT NULL;
ALTER TABLE followers ADD COLUMN postal_code VARCHAR(10) DEFAULT NULL;
ALTER TABLE followers ADD COLUMN accepted_terms_at DATETIME DEFAULT NULL;
ALTER TABLE fan_favorite_bands ADD COLUMN image_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_bands ADD COLUMN image_thumb_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_bands ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_bands ADD COLUMN in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_bands ADD COLUMN publish_at DATETIME DEFAULT NULL;
ALTER TABLE fan_favorite_actors ADD COLUMN image_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_actors ADD COLUMN image_thumb_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_actors ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_actors ADD COLUMN in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_actors ADD COLUMN publish_at DATETIME DEFAULT NULL;
ALTER TABLE fan_favorite_movies ADD COLUMN image_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_movies ADD COLUMN image_thumb_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE fan_favorite_movies ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_movies ADD COLUMN in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_movies ADD COLUMN publish_at DATETIME DEFAULT NULL;
ALTER TABLE timeline_posts ADD COLUMN title VARCHAR(100) DEFAULT NULL;
ALTER TABLE timeline_posts ADD COLUMN hashtags VARCHAR(300) DEFAULT NULL;
ALTER TABLE timeline_posts ADD COLUMN call_to_action VARCHAR(200) DEFAULT NULL;
ALTER TABLE timeline_posts ADD COLUMN redirect_link VARCHAR(500) DEFAULT NULL;
ALTER TABLE timeline_posts ADD COLUMN source VARCHAR(20) NOT NULL DEFAULT 'dashboard';
ALTER TABLE timeline_posts ADD COLUMN in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE favorite_tracks ADD COLUMN note TEXT DEFAULT NULL;
ALTER TABLE favorite_tracks ADD COLUMN image_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE favorite_tracks ADD COLUMN image_thumb_path VARCHAR(500) DEFAULT NULL;
ALTER TABLE favorite_tracks ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE favorite_tracks ADD COLUMN in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE favorite_tracks ADD COLUMN publish_at DATETIME DEFAULT NULL;

-- ============================================================
-- PARTE 2: tabelle nuove (create nell'ordine dello schema.sql originale,
-- per rispettare le dipendenze delle FOREIGN KEY)
-- ============================================================
CREATE TABLE IF NOT EXISTS special_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price_label VARCHAR(100) DEFAULT NULL,
    cover_path VARCHAR(255) DEFAULT NULL,
    valid_from DATETIME DEFAULT NULL,
    valid_until DATETIME DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS photo_albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    cover_path VARCHAR(255) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS photo_album_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES photo_albums(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    cover_path VARCHAR(255) DEFAULT NULL,
    accepts_inquiries TINYINT(1) NOT NULL DEFAULT 1,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    guest_name VARCHAR(120) NOT NULL,
    guest_email VARCHAR(190) NOT NULL,
    guest_phone VARCHAR(30) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS table_reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NULL,
    guest_name VARCHAR(120) NOT NULL,
    guest_email VARCHAR(190) NOT NULL,
    guest_phone VARCHAR(30) DEFAULT NULL,
    party_size SMALLINT UNSIGNED NOT NULL,
    notes VARCHAR(300) DEFAULT NULL,
    status ENUM('pending','confirmed','declined','cancelled','no_show','completed') NOT NULL DEFAULT 'confirmed',
    marketing_opt_in TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    INDEX idx_owner_status (user_id, status),
    INDEX idx_guest_email (guest_email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_slug (user_id, slug),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blog_post_categories (
    post_id INT NOT NULL,
    category_id INT NOT NULL,
    PRIMARY KEY (post_id, category_id),
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spotify_playlist_id VARCHAR(50) NOT NULL,
    playlist_name VARCHAR(200) NOT NULL,
    playlist_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_playlist (user_id, spotify_playlist_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spotify_album_id VARCHAR(50) NOT NULL,
    album_name VARCHAR(200) NOT NULL,
    album_artist_name VARCHAR(200) DEFAULT NULL,
    album_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_album (user_id, spotify_album_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    google_books_id VARCHAR(50) NOT NULL,
    book_title VARCHAR(200) NOT NULL,
    book_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_book (user_id, google_books_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crossref_doi VARCHAR(191) NOT NULL,
    publication_title VARCHAR(500) NOT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_publication (user_id, crossref_doi),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spoonacular_recipe_id VARCHAR(50) NOT NULL,
    recipe_title VARCHAR(200) NOT NULL,
    recipe_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    cached_details TEXT DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_recipe (user_id, spoonacular_recipe_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    thesportsdb_team_id VARCHAR(50) NOT NULL,
    team_name VARCHAR(200) NOT NULL,
    team_badge VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_team (user_id, thesportsdb_team_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    thesportsdb_player_id VARCHAR(50) NOT NULL,
    player_name VARCHAR(200) NOT NULL,
    player_photo VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_player (user_id, thesportsdb_player_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    thesportsdb_event_id VARCHAR(50) NOT NULL,
    match_title VARCHAR(200) NOT NULL,
    match_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_match (user_id, thesportsdb_event_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fan_favorite_trips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    place_name VARCHAR(200) NOT NULL,
    address VARCHAR(500) DEFAULT NULL,
    lat DECIMAL(10,7) NOT NULL,
    lng DECIMAL(10,7) NOT NULL,
    map_image_path VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) NOT NULL DEFAULT 1,
    in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(100) NOT NULL,
    token_hash VARCHAR(64) NOT NULL UNIQUE,
    token_prefix VARCHAR(40) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at DATETIME DEFAULT NULL,
    last_used_at DATETIME DEFAULT NULL,
    -- Nome con cui questo token è registrato sul server MCP (vedi admin_mcp.php) — NULL per i
    -- token creati normalmente da Dashboard -> API, non legati a nessuna integrazione MCP.
    mcp_name VARCHAR(100) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS api_request_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_token_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    method VARCHAR(10) NOT NULL,
    endpoint VARCHAR(200) NOT NULL,
    status_code SMALLINT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (api_token_id) REFERENCES api_tokens(id) ON DELETE SET NULL,
    INDEX idx_token_time (api_token_id, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profile_navigation_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(50) NULL,
    url VARCHAR(255) NOT NULL,
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_name (user_id, name),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description VARCHAR(300) DEFAULT NULL,
    price DECIMAL(6,2) DEFAULT NULL,
    allergens VARCHAR(60) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    primary_color VARCHAR(7) NOT NULL,
    deep_color VARCHAR(7) NOT NULL,
    light_color VARCHAR(7) NOT NULL,
    accent_color VARCHAR(7) NOT NULL,
    text_primary VARCHAR(7) NOT NULL DEFAULT '#1A1A1A',
    text_secondary VARCHAR(7) NOT NULL DEFAULT '#757575',
    success_color VARCHAR(7) NOT NULL DEFAULT '#4CAF50',
    error_color VARCHAR(7) NOT NULL DEFAULT '#F44336',
    is_preset BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pinned_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_type VARCHAR(30) NOT NULL,
    content_id INT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    pinned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_content (user_id, content_type, content_id),
    KEY idx_user_sort (user_id, sort_order),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dashboard_tab_order (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tab_key VARCHAR(30) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_user_tab (user_id, tab_key),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
