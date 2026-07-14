# Rendere pubblico il repository GitHub

## Su GitHub

1. Vai su https://github.com/posillipo/myband
2. Clicca **Settings** (in alto nella barra del repository, non le impostazioni dell'account)
3. Scorri fino in fondo alla pagina, sezione **"Danger Zone"**
4. Clicca **Change repository visibility**
5. Seleziona **Change to public**
6. GitHub ti chiede conferma: devi digitare il nome del repository (`posillipo/myband`) per
   confermare, poi clicca **I understand, change repository visibility**

## Su Portainer

Torna sulla schermata "Add stack" (o modifica lo stack se l'avevi già impostato):
1. Nella sezione **Authentication**, disattiva il toggle (non serve più alcun token)
2. Lascia il resto della configurazione com'era:
   - Repository URL: `https://github.com/posillipo/myband.git`
   - Repository reference: `refs/heads/main`
   - Compose path: `docker-compose.yml`
   - Environment variables: `DB_PASSWORD`, `DB_ROOT_PASSWORD`, `SITE_URL` già compilate
3. Clicca **Deploy the stack**

Questa volta il clone dovrebbe funzionare senza errori di autenticazione, dato che un
repository pubblico non richiede credenziali per essere letto.

## Nota sulla sicurezza

Rendere pubblico il repository va bene in questo caso perché:
- Il file `.env` con le password del database è escluso da Git (`.gitignore`)
- I dati degli utenti (iscrizioni, brani caricati, ecc.) restano solo sul database del server,
  mai dentro il repository
- Il codice PHP in sé non contiene segreti o credenziali

Se in futuro dovessi aggiungere per errore un file con password o chiavi API, ricordati di
rimuoverlo dal repository e, soprattutto, di cambiare comunque quella password/chiave (una volta
pubblicata su un repository pubblico va considerata compromessa anche se poi rimossa).
