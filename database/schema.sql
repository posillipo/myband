CREATE DATABASE IF NOT EXISTS myband CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE myband;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    email_verified TINYINT(1) NOT NULL DEFAULT 1,
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_expires DATETIME DEFAULT NULL,
    legacy_gestore_id INT DEFAULT NULL,
    legacy_band_id INT DEFAULT NULL,
    legacy_stato VARCHAR(20) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS profiles (
    user_id INT PRIMARY KEY,
    display_name VARCHAR(120) NOT NULL,
    bio TEXT,
    avatar_path VARCHAR(255),
    theme_color VARCHAR(7) DEFAULT '#6C5CE7',
    dashboard_theme VARCHAR(10) NOT NULL DEFAULT 'dark',
    spotify_artist_id VARCHAR(50) DEFAULT NULL,
    spotify_artist_name VARCHAR(200) DEFAULT NULL,
    genere VARCHAR(100) DEFAULT NULL,
    citta VARCHAR(100) DEFAULT NULL,
    provincia VARCHAR(50) DEFAULT NULL,
    telefono VARCHAR(50) DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(120) NOT NULL,
    url VARCHAR(500) NOT NULL,
    icon VARCHAR(40) DEFAULT 'link',
    sort_order INT NOT NULL DEFAULT 0,
    click_count INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_website_icon TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audio_tracks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    venue VARCHAR(150),
    city VARCHAR(100),
    event_date DATETIME NOT NULL,
    ticket_url VARCHAR(500),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    excerpt VARCHAR(300),
    content TEXT NOT NULL,
    published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_user_slug (user_id, slug)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_name VARCHAR(120) NOT NULL,
    sender_email VARCHAR(190) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Impostazioni globali del sito (chiave/valore), es. script privacy/cookie da iniettare in tutte le pagine pubbliche
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(60) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB;

-- Token per il login "ricordami": selector in chiaro (per la ricerca), validator solo come hash
-- (mai in chiaro nel database), seguendo il pattern standard selector/validator per i cookie
-- di login persistenti.
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(24) NOT NULL UNIQUE,
    validator_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Follower "leggeri" (solo email, nessun account) di un artista. Il token serve sia per
-- confermare l'iscrizione (doppio opt-in anti-spam) sia, dopo la conferma, come link di
-- disiscrizione in ogni email inviata — un solo utilizzo per entrambi gli scopi.
CREATE TABLE IF NOT EXISTS followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(190) NOT NULL,
    verified TINYINT(1) NOT NULL DEFAULT 0,
    token VARCHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_email (user_id, email),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('privacy_script', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('gtm_head_script', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('gtm_body_script', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('fb_pixel_script', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_host', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_port', '587');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_user', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_pass', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_secure', 'tls');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_from', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_from_name', 'myband.it');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_verify_cert', '1');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('ga_measurement_id', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('privacy_policy_url', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('spotify_client_id', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('spotify_client_secret', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('spotify_app_token', '');
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('spotify_app_token_expires', '');
