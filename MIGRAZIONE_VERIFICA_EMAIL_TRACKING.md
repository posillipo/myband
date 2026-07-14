# Migrazione database — verifica email + tracking

Questa release aggiunge colonne alla tabella `users` e nuove chiavi a `site_settings`. Va
applicato **un solo comando SQL** sul database di produzione dopo il redeploy del codice.

## Comando da eseguire

Via Portainer → Containers → `myband_db` → Console → `mysql -u myband_user -p myband` (vedi
`COME_DIVENTARE_ADMIN.md` per i dettagli su come collegarti), poi incolla:

```sql
ALTER TABLE users
  ADD COLUMN email_verified TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL,
  ADD COLUMN verification_expires DATETIME DEFAULT NULL;

INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('gtm_head_script', ''),
  ('gtm_body_script', ''),
  ('fb_pixel_script', '');
```

## Perché è sicuro per gli utenti già esistenti

La colonna `email_verified` viene aggiunta con `DEFAULT 1`: tutti gli utenti già registrati
(te compreso) vengono automaticamente marcati come "già verificati" e **non perdono l'accesso**.
Solo le **nuove registrazioni** da questo momento in poi partiranno con `email_verified = 0` e
dovranno confermare l'email per poter accedere.

## Ordine delle operazioni

1. Push del codice su GitHub (come sempre)
2. Su Portainer: **Pull and redeploy** dello stack
3. Esegui il comando SQL sopra (una tantum)
4. Testa una nuova registrazione di prova per verificare il flusso di conferma email

## Verifica che sia andato a buon fine

```sql
DESCRIBE users;
```
Devi vedere le tre nuove colonne (`email_verified`, `verification_token`,
`verification_expires`) nell'elenco.

```sql
SELECT setting_key FROM site_settings;
```
Devi vedere `privacy_script`, `gtm_head_script`, `gtm_body_script`, `fb_pixel_script`.
