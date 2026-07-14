# Migrazione applicata con successo

## Esito

- `ALTER TABLE users` → colonne `email_verified`, `verification_token`, `verification_expires`
  aggiunte correttamente (0 rows affected è normale per un ALTER TABLE su struttura, non su dati;
  il warning riguarda solo la sintassi deprecata `TINYINT(1)`, innocuo)
- `INSERT IGNORE INTO site_settings` → 3 righe inserite (`gtm_head_script`, `gtm_body_script`,
  `fb_pixel_script`)

## Prossimo passo: test registrazione

1. Apri `https://www.myband.it/register.php` (meglio in una finestra in incognito/privata)
2. Registra un account di prova
3. Dovresti vedere: "Registrazione completata! Ti abbiamo inviato un'email di conferma..."
4. Se **non hai ancora configurato SendPulse**: nessuna email arriverà davvero — è normale, il
   sistema di invio è "silenzioso" finché SMTP_HOST non è impostato
5. Per attivare comunque l'account di prova: **Area Admin → Utenti** → trova l'account appena
   creato → pulsante **"Verifica email"** (verifica manuale, bypassa l'email)
6. Prova il login con l'account appena verificato: deve funzionare

## Quando vuoi completare SendPulse

Appena mi dai le credenziali SMTP di SendPulse (host, porta, login, password), completo la
configurazione e da quel momento le email di conferma/notifica partiranno automaticamente,
senza bisogno della verifica manuale da admin.
