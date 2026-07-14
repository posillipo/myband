# Preparare l'ambiente su Hetzner e collegarlo a GitHub

Questa guida copre tutto il percorso: creare il server, installare Docker, clonare il repository
da GitHub, avviare l'app, collegare il dominio myband.it con HTTPS, e impostare il workflow di
aggiornamento futuro via `git pull`.

Ho già modificato `docker-compose.yml`: la porta 8080 ora è legata solo a `127.0.0.1` (non più
esposta su tutta la rete) — in produzione il traffico pubblico passerà sempre attraverso Nginx
con HTTPS, mai direttamente sulla porta interna del container.

---

## 1. Creare il server su Hetzner Cloud

1. Vai su https://console.hetzner.cloud e crea un nuovo progetto (se non esiste già)
2. "Add Server":
   - **Location**: una vicina a te/ai tuoi utenti (es. Norimberga o Falkenstein per l'Italia)
   - **Image**: Ubuntu 24.04
   - **Type**: CX22 (2 vCPU / 4GB RAM) è più che sufficiente per iniziare; puoi scalare dopo
   - **SSH Key**: aggiungi la tua chiave pubblica invece della password (più sicuro)
     ```bash
     # se non hai già una chiave, generala in WSL:
     ssh-keygen -t ed25519 -C "gianluca@myband.it"
     cat ~/.ssh/id_ed25519.pub   # incolla questo output nel campo "SSH Key" di Hetzner
     ```
3. Crea il server e annota l'IP pubblico assegnato

## 2. Primo accesso e messa in sicurezza di base

```bash
ssh root@IP_DEL_SERVER

# aggiorna il sistema
apt update && apt upgrade -y

# crea un utente non-root per operare (evita di lavorare sempre come root)
adduser gianluca
usermod -aG sudo gianluca

# firewall di base: consenti solo SSH, HTTP, HTTPS
apt install -y ufw
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

Da questo punto in poi collegati con `ssh gianluca@IP_DEL_SERVER` invece che come root.

## 3. Installare Docker

```bash
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER
# disconnettiti e riconnettiti via SSH perché il gruppo abbia effetto
exit
ssh gianluca@IP_DEL_SERVER

docker --version
docker compose version
```

## 4. Collegare GitHub al server (deploy key, sola lettura)

Meglio non usare il tuo Personal Access Token personale sul server: crea una **deploy key**
dedicata, di sola lettura, valida solo per questo repository.

```bash
ssh-keygen -t ed25519 -C "hetzner-myband-deploy" -f ~/.ssh/myband_deploy -N ""
cat ~/.ssh/myband_deploy.pub
```

Copia l'output, poi su GitHub:
1. Vai sul repository → **Settings** → **Deploy keys** → **Add deploy key**
2. Incolla la chiave pubblica, lascia **NON** spuntato "Allow write access" (sola lettura basta)

Poi sul server, configura SSH per usare questa chiave con GitHub:
```bash
cat >> ~/.ssh/config << 'EOF'
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/myband_deploy
EOF
chmod 600 ~/.ssh/config
```

## 5. Clonare il repository e avviare l'app

```bash
sudo mkdir -p /opt/myband
sudo chown $USER:$USER /opt/myband
git clone git@github.com:TUO-USERNAME/myband-platform.git /opt/myband
cd /opt/myband

cp .env.example .env
nano .env   # password FORTI e diverse da quelle di test, per la produzione

docker compose up -d --build
docker compose ps
```

A questo punto il sito risponde solo su `http://127.0.0.1:8080` **dentro** il server (non
raggiungibile dall'esterno). Nei prossimi passi lo colleghiamo al dominio con Nginx + HTTPS.

## 6. Puntare il dominio myband.it al server

Dal pannello DNS del tuo registrar, crea:
```
Tipo  A     Nome: @      Valore: IP_DEL_SERVER
Tipo  A     Nome: www    Valore: IP_DEL_SERVER
```
La propagazione può richiedere da pochi minuti a qualche ora. Verifica con:
```bash
dig +short myband.it
```

## 7. Installare Nginx come reverse proxy + Certbot per HTTPS

```bash
sudo apt install -y nginx certbot python3-certbot-nginx

sudo tee /etc/nginx/sites-available/myband.it << 'EOF'
server {
    listen 80;
    server_name myband.it www.myband.it;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        client_max_body_size 25M;
    }
}
EOF

sudo ln -s /etc/nginx/sites-available/myband.it /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# certificato HTTPS gratuito, con rinnovo automatico già configurato da Certbot
sudo certbot --nginx -d myband.it -d www.myband.it
```

`client_max_body_size 25M` è necessario perché l'app accetta upload audio fino a 20MB.

## 8. Verifica finale

```bash
curl -I https://myband.it
```
Dovresti vedere `HTTP/2 200`. Apri il browser su `https://myband.it` e controlla che il
lucchetto HTTPS sia presente.

## 9. Workflow di aggiornamento (come da DEPLOY_UPDATE.md)

```bash
cd /opt/myband
git pull origin main
docker compose up -d --build
```

Per i backup periodici del database:
```bash
docker compose exec db mysqldump -u root -p myband > /opt/myband/backups/backup_$(date +%F).sql
```
(crea prima la cartella `mkdir -p /opt/myband/backups`, ed è consigliabile programmare questo
comando con `cron` per farlo girare ogni notte — se vuoi te lo preparo)

## 10. Checklist riassuntiva

- [ ] Server Hetzner creato, accesso SSH con chiave (no password)
- [ ] Firewall UFW attivo (solo SSH/80/443)
- [ ] Docker + Compose installati
- [ ] Deploy key GitHub configurata (sola lettura)
- [ ] Repository clonato in `/opt/myband`, `.env` di produzione configurato
- [ ] Container `app` e `db` attivi (`docker compose ps`)
- [ ] DNS di myband.it punta all'IP del server
- [ ] Nginx reverse proxy configurato e testato
- [ ] Certificato HTTPS attivo (Certbot)
- [ ] Primo backup del database effettuato
