# Deploy su Hetzner con Portainer + Nginx Proxy Manager

Questa guida sostituisce la sezione Nginx/Certbot di `HETZNER_DEPLOY.md`: il tuo server Hetzner
gestisce già tutto tramite **Portainer** (orchestrazione container) e **Nginx Proxy Manager**
(reverse proxy + SSL), quindi seguiamo lo stesso schema già in uso per gli altri siti
(gianlucadipietro.com, Etherna, ecc.).

## 0. Prima di iniziare: libera il container myband.it esistente

Dallo screenshot vedo lo stack WordPress `wordpress_myband_it` (container
`wordpress_myband_it-db-myband-1` e `wordpress_myband_it-wordpress...-1`, porta 8082).
Su Portainer:
1. **Stacks** → apri lo stack `wordpress_myband_it`
2. **Stop** (puoi anche fare un backup del DB prima, se contiene contenuti da conservare)
3. Una volta confermato che non serve più, **Remove** lo stack

Questo libera anche la porta **8082**, che quindi resta disponibile se preferisci riutilizzarla
al posto di 8085 (in questa guida uso 8085 per sicurezza, così non devi aspettare la rimozione
prima di procedere con gli altri passaggi).

## 1. Assicurati che il codice sia su GitHub

Se non l'hai ancora fatto, segui `GITHUB_SETUP.md`. Portainer clonerà il repository direttamente
da lì.

## 2. Crea lo Stack in Portainer, dal repository GitHub

1. Nel menu laterale di Portainer: **Stacks** → **Add stack**
2. **Name**: `myband`
3. **Build method**: scegli **Repository**
4. **Repository URL**: `https://github.com/TUO-USERNAME/myband-platform.git`
5. **Repository reference**: `refs/heads/main`
6. **Compose path**: `docker-compose.yml`
7. Se il repository è **privato**, in "Authentication" spunta e inserisci:
   - Username: il tuo username GitHub
   - Personal Access Token: un token con scope `repo` (vedi `GITHUB_SETUP.md` per come generarlo)
8. **Environment variables** (sezione più in basso nella stessa pagina): aggiungi qui le variabili
   invece di creare un file `.env` — Portainer le inietta automaticamente nello stack:
   ```
   DB_PASSWORD = una-password-forte-di-produzione
   DB_ROOT_PASSWORD = un-altra-password-forte-diversa
   SITE_URL = https://myband.it
   ```
9. Clicca **Deploy the stack**

Portainer clona il repository, builda l'immagine `app` (Dockerfile incluso) e avvia i container
`myband_app` e `myband_db`, esattamente come faceva `docker compose up -d --build` da riga di
comando — solo gestito dall'interfaccia che già usi per tutto il resto.

## 3. Verifica che i container siano su

In Portainer, nella lista principale dei container dovresti vedere comparire:
- `myband_app` — running
- `myband_db` — running, senza porta pubblica esposta (come i tuoi altri DB MariaDB)

La porta pubblicata sarà **8085** (o quella che hai scelto).

## 4. Configura il Proxy Host in Nginx Proxy Manager

> Suggerimento: puoi verificare il valore di "Forward Hostname/IP" (punto 4 sotto) in qualsiasi
> momento, anche subito, indipendentemente dagli altri passaggi — non c'è un ordine obbligato.
> Basta aprire un Proxy Host esistente (es. quello di gianlucadipietro.com) e copiare quel valore.

Apri l'interfaccia di Nginx Proxy Manager (porta 81 del tuo container `proxy-manager-app-1`):

1. **Proxy Hosts** → **Add Proxy Host**
2. **Domain Names**: `myband.it`, `www.myband.it`
3. **Scheme**: `http`
4. **Forward Hostname/IP**: usa lo stesso valore che hai già impostato per gli altri Proxy Host
   (es. quello configurato per `gianlucadipietro.com` o `wordpress_myband_it`) — apri uno di quei
   Proxy Host esistenti per copiare esattamente l'IP/hostname che usi lì, così sei sicuro che
   Nginx Proxy Manager riesca a raggiungere i container allo stesso modo
5. **Forward Port**: `8085`
6. Tab **SSL**: seleziona "Request a new SSL Certificate", spunta "Force SSL" e "HTTP/2 Support"
7. Salva

## 5. DNS

Come già indicato in precedenza, punta il dominio all'IP del server:
```
Tipo A   Nome: @      Valore: IP_DEL_SERVER
Tipo A   Nome: www    Valore: IP_DEL_SERVER
```

## 6. Verifica finale

Apri `https://myband.it` nel browser: dovresti vedere la landing page con il lucchetto HTTPS
attivo (certificato gestito automaticamente da Nginx Proxy Manager, rinnovo incluso).

## 7. Aggiornare il sito in futuro (il vantaggio di Portainer)

Con lo stack collegato al repository GitHub, l'aggiornamento è molto più semplice che via SSH:

1. Fai push delle modifiche sul repository GitHub (io ti darò sempre il codice pronto)
2. Su Portainer: **Stacks** → `myband` → pulsante **Pull and redeploy**
3. Portainer scarica l'ultima versione del codice e ricostruisce solo il container `app`

Se preferisci l'automazione completa, nella pagina dello stack puoi attivare **"Automatic
updates"** con un intervallo di polling, oppure generare un **webhook** che GitHub può chiamare
automaticamente ad ogni push (in Settings dello stack → Webhook). Se ti interessa configurarlo,
dimmelo e ti preparo i passaggi.

> Nota importante: come già spiegato in `DEPLOY_UPDATE.md`, questo aggiorna solo il **codice**.
> Se una modifica cambia lo schema del database, ti fornirò comunque il comando SQL da eseguire
> a mano — su Portainer puoi farlo dalla console del container `myband_db` (icona ">_" nella
> lista container) senza bisogno di SSH.

## 8. Backup del database via Portainer

Dalla lista container, apri la console (icona ">_") di `myband_db` ed esegui:
```bash
mysqldump -u root -p myband > /tmp/backup.sql
```
Poi puoi scaricare il file dalla sezione "Volumes" o copiarlo altrove. Se preferisci un backup
automatico schedulato, possiamo aggiungere un piccolo container di backup allo stack — fammi
sapere se ti interessa.
