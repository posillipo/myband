# Notifiche email per i nuovi messaggi di contatto

Quando qualcuno invia un messaggio dal form Contatti di una pagina artista, il musicista
proprietario di quella pagina riceve ora un'email di notifica alla sua email di registrazione
(quella con cui si è iscritto a myband.it), con nome, email e testo del messaggio del mittente.

Se non configuri l'SMTP, non succede nulla di rotto: il messaggio resta comunque salvato e
visibile in dashboard, semplicemente non arriva la notifica via email.

## Cosa ti serve

Un account SMTP da cui inviare le email. Alcune opzioni comuni:

### Opzione A — Gmail (comodo se hai già un account Google)
1. Attiva la verifica in due passaggi sul tuo account Google (necessaria per il passo 2)
2. Genera una **App Password** su https://myaccount.google.com/apppasswords
3. Valori da usare:
   ```
   SMTP_HOST = smtp.gmail.com
   SMTP_PORT = 587
   SMTP_USER = tuonome@gmail.com
   SMTP_PASS = (la App Password generata, 16 caratteri)
   SMTP_SECURE = tls
   SMTP_FROM = tuonome@gmail.com
   SMTP_FROM_NAME = myband.it
   ```

### Opzione B — Un servizio email transazionale (più professionale, consigliato in produzione)
Servizi come **Brevo (ex Sendinblue)**, **Mailgun**, **Amazon SES** offrono piani gratuiti per
basso volume e sono pensati apposta per l'invio automatico da applicazioni. Ognuno fornisce le
proprie credenziali SMTP nella rispettiva dashboard (host, porta, utente, password) — se scegli
uno di questi, dimmi quale e ti preparo i valori esatti.

### Opzione C — SMTP del tuo hosting/dominio esistente
Se hai già una casella email su un dominio che gestisci (es. tramite Aruba, dove hai altri
progetti), puoi usare quella:
```
SMTP_HOST = smtp.tuoprovider.it (controlla la documentazione del tuo hosting)
SMTP_PORT = 587 (o 465 per SSL)
SMTP_USER = notifiche@tuodominio.it
SMTP_PASS = password della casella email
SMTP_SECURE = tls (o ssl se usi la porta 465)
SMTP_FROM = notifiche@tuodominio.it
```

## Come configurarlo su Portainer (via GitHub, nessuna modifica manuale)

1. Fai push di questo codice aggiornato su GitHub (come sempre: `git add -A`, `git commit`,
   `git push`)
2. Su Portainer: **Stacks** → `myband` → **Editor** (o "Update the stack")
3. Nella sezione **Environment variables**, aggiungi le nuove voci (oltre a quelle già presenti
   `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `SITE_URL`):
   ```
   SMTP_HOST = (il valore scelto sopra)
   SMTP_PORT = 587
   SMTP_USER = ...
   SMTP_PASS = ...
   SMTP_SECURE = tls
   SMTP_FROM = ...
   SMTP_FROM_NAME = myband.it
   ```
4. **Update the stack** (o Pull and redeploy se hai anche aggiornato il codice)

## Come testare che funzioni

1. Apri la pagina pubblica di un tuo profilo di test: `https://www.myband.it/tuoslug/contatti`
2. Invia un messaggio di prova dal form
3. Controlla la casella email associata a quell'account (quella di registrazione) — dovrebbe
   arrivare l'email entro pochi secondi
4. Se non arriva, controlla i log del container `myband_app` in Portainer: eventuali errori SMTP
   vengono scritti lì (cerca righe che iniziano con `[SimpleSmtpMailer]`)

## Note tecniche

- L'invio è "best effort": se l'email fallisce per qualsiasi motivo (credenziali sbagliate,
  provider irraggiungibile, ecc.), il messaggio di contatto **resta comunque salvato** nel
  database e visibile in dashboard — non blocca né rallenta l'esperienza di chi scrive
- Non è richiesta nessuna libreria esterna: ho scritto un piccolo client SMTP autonomo
  (`app/src/mailer.php`) per non dover gestire dipendenze Composer nel build Docker
- Supporta sia STARTTLS (porta 587, la più comune) sia SSL implicito (porta 465)
