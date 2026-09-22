-- ============================================================
-- 24. Modulo Menù (categorie e piatti con allergeni, disponibile per qualsiasi tipo di account)
-- ============================================================
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

-- ============================================================
-- 25. Menu di Navigazione (nascondi singoli tab standard dal menu pubblico)
-- ============================================================
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

-- ============================================================
-- 26. Prenotazione tavoli per gli eventi
-- ============================================================
ALTER TABLE events ADD COLUMN IF NOT EXISTS accepts_reservations TINYINT(1) NOT NULL DEFAULT 0;

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

-- ============================================================
-- 27. Separatori e mappa (gratuita, OpenStreetMap) tra i Link in Bio
-- ============================================================
ALTER TABLE links
  ADD COLUMN IF NOT EXISTS link_type ENUM('link','divider','map') NOT NULL DEFAULT 'link',
  ADD COLUMN IF NOT EXISTS map_lat DECIMAL(10,7) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS map_lng DECIMAL(10,7) DEFAULT NULL;

-- ============================================================
-- 28. Link personalizzato per i post (redirect via JS sulla pagina, non nel feed)
-- ============================================================
ALTER TABLE profiles
  ADD COLUMN IF NOT EXISTS custom_feed_guid VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS custom_feed_guid_since DATETIME DEFAULT NULL;

-- ============================================================
-- 29. Profili multipli per utente ("Crea nuovo profilo")
-- ============================================================
ALTER TABLE profile_admins ADD COLUMN IF NOT EXISTS role ENUM('coadmin','owner') NOT NULL DEFAULT 'coadmin';

-- ============================================================
-- 30. Privacy/Cookie e Tracking personalizzabili per profilo
-- ============================================================
ALTER TABLE profiles ADD COLUMN IF NOT EXISTS privacy_tracking_settings TEXT DEFAULT NULL;

-- ============================================================
-- 31. Assistente AI (Google Gemini) per generare i testi della Timeline
-- ============================================================
-- (nessun comando SQL: solo codice applicativo, o riuso di colonna esistente)

-- ============================================================
-- 32. Miniatura leggera per le foto della Timeline (`timeline_posts.image_thumb_path`)
-- ============================================================
ALTER TABLE timeline_posts ADD COLUMN IF NOT EXISTS image_thumb_path VARCHAR(255) DEFAULT NULL;

-- ============================================================
-- 33. Nuovo modulo "Attori che amo" (`fan_favorite_actors`)
-- ============================================================
CREATE TABLE IF NOT EXISTS fan_favorite_actors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tmdb_person_id VARCHAR(50) NOT NULL,
    actor_name VARCHAR(200) NOT NULL,
    actor_image VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_actor (user_id, tmdb_person_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 34. Nuovo modulo "Film che amo" (`fan_favorite_movies`)
-- ============================================================
CREATE TABLE IF NOT EXISTS fan_favorite_movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tmdb_movie_id VARCHAR(50) NOT NULL,
    movie_title VARCHAR(200) NOT NULL,
    movie_image VARCHAR(500) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_movie (user_id, tmdb_movie_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 35. Nota personale + pagina di dettaglio condivisibile per Band/Attori/Film che amo
-- ============================================================
ALTER TABLE fan_favorite_bands ADD COLUMN IF NOT EXISTS note TEXT DEFAULT NULL;
ALTER TABLE fan_favorite_actors ADD COLUMN IF NOT EXISTS note TEXT DEFAULT NULL;
ALTER TABLE fan_favorite_movies ADD COLUMN IF NOT EXISTS note TEXT DEFAULT NULL;

-- ============================================================
-- 36. Controllo visibilità nel Feed per Band/Attori/Film che amo
-- ============================================================
ALTER TABLE fan_favorite_bands ADD COLUMN IF NOT EXISTS show_in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_actors ADD COLUMN IF NOT EXISTS show_in_feed TINYINT(1) NOT NULL DEFAULT 1;
ALTER TABLE fan_favorite_movies ADD COLUMN IF NOT EXISTS show_in_feed TINYINT(1) NOT NULL DEFAULT 1;

-- ============================================================
-- 37. Logica di pubblicazione stile Timeline per Band/Attori/Film/Brani che amo
-- ============================================================
ALTER TABLE fan_favorite_bands
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS image_thumb_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS publish_at DATETIME DEFAULT NULL;

ALTER TABLE fan_favorite_actors
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS image_thumb_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS publish_at DATETIME DEFAULT NULL;

ALTER TABLE fan_favorite_movies
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS image_thumb_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS publish_at DATETIME DEFAULT NULL;

ALTER TABLE favorite_tracks
  ADD COLUMN IF NOT EXISTS note TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN IF NOT EXISTS image_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS image_thumb_path VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS publish_at DATETIME DEFAULT NULL;

UPDATE profile_navigation_menu SET name = 'Brani che amo' WHERE name = 'Brani';

-- ============================================================
-- 38. Sincronizzazione Cinema: film in programmazione nel modulo Link
-- ============================================================
ALTER TABLE profiles
  ADD COLUMN IF NOT EXISTS cinema_films_json_url VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS cinema_films_synced_at DATETIME DEFAULT NULL;

ALTER TABLE links
  MODIFY COLUMN link_type ENUM('link','divider','map','film') NOT NULL DEFAULT 'link',
  ADD COLUMN IF NOT EXISTS external_ref VARCHAR(64) DEFAULT NULL,
  ADD UNIQUE KEY uniq_user_external_ref (user_id, external_ref);

-- ============================================================
-- 39. Nuovo modulo "Libri che amo" (Google Books API)
-- ============================================================
CREATE TABLE IF NOT EXISTS fan_favorite_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    google_books_id VARCHAR(50) NOT NULL,
    book_title VARCHAR(200) NOT NULL,
    book_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_book (user_id, google_books_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 40. Nuovo modulo "Viaggi"
-- ============================================================
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
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 41. Nuovo modulo "Playlist che amo"
-- ============================================================
CREATE TABLE IF NOT EXISTS fan_favorite_playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spotify_playlist_id VARCHAR(50) NOT NULL,
    playlist_name VARCHAR(200) NOT NULL,
    playlist_image VARCHAR(500) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    image_path VARCHAR(500) DEFAULT NULL,
    image_thumb_path VARCHAR(500) DEFAULT NULL,
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_playlist (user_id, spotify_playlist_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 42. Nuovo modulo "Album che amo"
-- ============================================================
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
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
    publish_at DATETIME DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_album (user_id, spotify_album_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 43. Fino a 10 foto per post Timeline (carosello stile Instagram)
-- ============================================================
CREATE TABLE IF NOT EXISTS timeline_post_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES timeline_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 44. Fino a 10 foto anche per il modulo "Viaggi che amo"
-- ============================================================
CREATE TABLE IF NOT EXISTS fan_favorite_trip_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trip_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (trip_id) REFERENCES fan_favorite_trips(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 45. Sezione Eventi: descrizione + modifica + copertina non ritagliata
-- ============================================================
ALTER TABLE events ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL AFTER ticket_url;

-- ============================================================
-- 46. Eventi perpetui e ricorrenti (Lun-Ven / Weekend)
-- ============================================================
ALTER TABLE events ADD COLUMN IF NOT EXISTS is_perpetual TINYINT(1) NOT NULL DEFAULT 0 AFTER event_date;
ALTER TABLE events ADD COLUMN IF NOT EXISTS recurrence ENUM('none','weekdays','weekend') NOT NULL DEFAULT 'none' AFTER is_perpetual;

-- ============================================================
-- 47. Fuso orario del profilo (nessuna migrazione: riuso di una colonna inutilizzata)
-- ============================================================
-- (nessun comando SQL: solo codice applicativo, o riuso di colonna esistente)

-- ============================================================
-- 48. Nuovo modulo "Offerte speciali"
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
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 49. Nuovo modulo "Foto e Album" (2/3 delle nuove funzionalità richieste)
-- ============================================================
CREATE TABLE IF NOT EXISTS photo_albums (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    cover_path VARCHAR(255) DEFAULT NULL,
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
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

-- ============================================================
-- 50. Nuovo modulo "Servizi" (3/3 delle nuove funzionalità richieste)
-- ============================================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    cover_path VARCHAR(255) DEFAULT NULL,
    accepts_inquiries TINYINT(1) NOT NULL DEFAULT 1,
    show_in_feed TINYINT(1) NOT NULL DEFAULT 1,
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

-- ============================================================
-- 51. Nuovo modulo "Pubblicazioni che amo" (CrossRef API)
-- ============================================================
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

-- ============================================================
-- 52. Data di aggiunta per i Link (`links.created_at`)
-- ============================================================
ALTER TABLE links ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- 53. API di sola lettura per i film Cinema + tool MCP
-- ============================================================
-- (nessun comando SQL: solo codice applicativo, o riuso di colonna esistente)

-- ============================================================
-- 54. Link di reindirizzamento fisso per singolo post Timeline (`timeline_posts.redirect_link`)
-- ============================================================
ALTER TABLE timeline_posts ADD COLUMN IF NOT EXISTS redirect_link VARCHAR(500) DEFAULT NULL AFTER call_to_action;
