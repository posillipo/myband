# Come applicare la migrazione — passo per passo, ora

Il file `MIGRAZIONE_VERIFICA_EMAIL_TRACKING.md` non è uno script da eseguire automaticamente:
è una **guida** che contiene il comando SQL da copiare e incollare a mano. Ecco i passaggi
concreti, in ordine.

## Passo 1 — Push del codice su GitHub (se non l'hai già fatto per l'ultima versione)

Da PowerShell, nella cartella del progetto:
```powershell
git add -A
git commit -m "Verifica email + admin dashboard + tracking"
git push
```

## Passo 2 — Redeploy su Portainer

Portainer → **Stacks** → `myband` → **Pull and redeploy**

## Passo 3 — Esegui il comando SQL (questo è il cuore della "migrazione")

1. Portainer → **Containers** → clicca su `myband_db`
2. Icona **Console** (">_") → Command `/bin/sh` → **Connect**
3. Nella console:
   ```bash
   mysql -u myband_user -p myband
   ```
4. Inserisci la password (vedi `RECUPERA_PASSWORD_DB.md` se non la ricordi)
5. Ora sei nel prompt `mysql>`. Incolla **tutto insieme** questo blocco e premi Invio:
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
6. Dovresti vedere conferme tipo `Query OK, ... rows affected` per ciascuna riga
7. Esci con `exit`

## Passo 4 — Verifica che sia andato a buon fine

Ancora nel prompt MySQL (o rientraci con lo stesso comando del passo 3):
```sql
DESCRIBE users;
```
Cerca nell'elenco le tre nuove colonne: `email_verified`, `verification_token`,
`verification_expires`.

## Passo 5 — Test pratico

1. Apri `https://www.myband.it/register.php` in una finestra in incognito
2. Registra un account di prova con un'email che puoi controllare davvero
3. Dovresti vedere il messaggio "registrazione completata, controlla la tua email"
4. Se hai già configurato l'SMTP (SendPulse): controlla che arrivi l'email con il link di
   conferma. Se non hai ancora configurato l'SMTP: nessuna email arriverà — in quel caso vai su
   Area Admin → Utenti, trova l'account di prova e clicca **"Verifica email"** per attivarlo
   manualmente

---

Se in un punto qualsiasi ricevi un errore, incollamelo così lo risolviamo insieme prima di andare
avanti al passo successivo.
