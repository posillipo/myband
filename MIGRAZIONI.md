# Registro migrazioni database

Elenco cronologico di tutte le modifiche allo schema applicate finora in produzione (già
eseguite). Utile come riferimento per sapere cosa contiene il database attuale, e come modello
per le prossime migrazioni.

Procedura standard per ogni voce: Portainer → Containers → `myband_db` → Console →
`mysql -u myband_user -p myband` (password: vedi `ADMIN_SETUP.md`), poi incolla il comando.

## 1. Ruolo admin (`is_admin` su `users`)
```sql
ALTER TABLE users ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;
```

## 2. Permalink SEO per il blog (`slug` su `blog_posts`)
```sql
ALTER TABLE blog_posts ADD COLUMN slug VARCHAR(180) NOT NULL DEFAULT '';
ALTER TABLE blog_posts ADD COLUMN excerpt VARCHAR(300);
ALTER TABLE blog_posts ADD UNIQUE KEY uniq_user_slug (user_id, slug);
```

## 3. Verifica email + tracking GTM/Pixel
```sql
ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN verification_expires DATETIME DEFAULT NULL;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('gtm_head_script', ''), ('gtm_body_script', ''), ('fb_pixel_script', '');
```

## 4. Configurazione SMTP da admin
```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('smtp_host', ''), ('smtp_port', '587'), ('smtp_user', ''), ('smtp_pass', ''),
  ('smtp_secure', 'tls'), ('smtp_from', ''), ('smtp_from_name', 'myband.it');
```

## 5. Opzione verifica certificato SSL
```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_verify_cert', '1');
```

## 6. Google Analytics
```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('ga_measurement_id', '');
```

## 7. Icona "sito web personale" sui link
```sql
ALTER TABLE links ADD COLUMN is_website_icon TINYINT(1) NOT NULL DEFAULT 0;
```

## 8. Tema dashboard band manager (scuro/chiaro)
```sql
ALTER TABLE profiles ADD COLUMN dashboard_theme VARCHAR(10) NOT NULL DEFAULT 'dark';
```

## 9. Login persistente "ricordami"
```sql
CREATE TABLE IF NOT EXISTS remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    selector VARCHAR(24) NOT NULL UNIQUE,
    validator_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

## 10. URL Privacy Policy per il footer pubblico
```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('privacy_policy_url', '');
```

## 11. Integrazione Spotify (collegamento artista + credenziali API)
```sql
ALTER TABLE profiles
  ADD COLUMN spotify_artist_id VARCHAR(50) DEFAULT NULL,
  ADD COLUMN spotify_artist_name VARCHAR(200) DEFAULT NULL;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('spotify_client_id', ''), ('spotify_client_secret', ''),
  ('spotify_app_token', ''), ('spotify_app_token_expires', '');
```

## 12. Segui via email
```sql
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
```

---

## Come aggiungere una nuova voce

Quando una futura modifica tocca lo schema, aggiungi qui una nuova sezione numerata con il
comando SQL esatto, PRIMA di eseguirlo in produzione — così questo file resta sempre lo
specchio fedele di cosa contiene il database.
