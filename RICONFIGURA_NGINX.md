# Spostare myband.it dal vecchio container WordPress al nuovo stack

Hai trovato la causa: il Proxy Host di `myband.it` in Nginx Proxy Manager punta ancora al vecchio
container WordPress (`wordpress_myband_it-wordpress...-1`, porta 8082). Va aggiornato per puntare
al nuovo stack (`myband_app`, porta 8085) e poi puoi dismettere il vecchio.

## 1. (Opzionale ma consigliato) Backup del vecchio sito WordPress

Se quel WordPress conteneva contenuti che vuoi conservare, prima di tutto fanne un backup.
Dalla console del container `wordpress_myband_it-db-myband-1` in Portainer:
```bash
mysqldump -u root -p --all-databases > /tmp/backup_wp_myband_it.sql
```
Scarica il file da Portainer prima di procedere, o copialo altrove.

## 2. Riconfigura il Proxy Host esistente in Nginx Proxy Manager

**Non crearne uno nuovo** — modifica quello già esistente per `myband.it`, così eviti conflitti
("Domain già in uso da un altro Proxy Host").

1. Apri Nginx Proxy Manager (porta 81)
2. **Proxy Hosts** → trova la riga con `myband.it`
3. Clicca sui tre puntini (o direttamente sulla riga) → **Edit**
4. Nel tab **Details**:
   - **Forward Hostname/IP**: lascialo invariato se puntava già all'IP/hostname corretto
     (verifica che sia lo stesso valore usato per gli altri tuoi siti)
   - **Forward Port**: cambialo da `8082` a **`8085`**
5. Salva

## 3. Verifica subito, prima di toccare il vecchio container

Apri `https://myband.it` nel browser: dovresti già vedere la nuova piattaforma (landing page
MyBand.it), non più WordPress. Il vecchio container WordPress può restare acceso in questo
momento — il traffico ormai non passa più da lì, Nginx Proxy Manager smista già verso il nuovo
container. Questo ti dà la sicurezza di testare senza fretta prima di eliminare nulla.

## 4. Una volta confermato che il nuovo sito funziona, dismetti il vecchio WordPress

In Portainer:
1. **Stacks** → apri lo stack che contiene `wordpress_myband_it-db-myband-1` e
   `wordpress_myband_it-wordpress...-1`
2. **Stop** (li spegne senza cancellarli — puoi ancora tornare indietro se serve)
3. Aspetta qualche giorno di utilizzo tranquillo del nuovo sito, poi se tutto va bene: **Remove**
   dello stack per liberare definitivamente le risorse (CPU/RAM/spazio disco) e la porta 8082

## 5. Se dopo il cambio porta il sito ancora non risponde

Rifai il test diretto sul container per isolare il problema:
```bash
curl -I http://127.0.0.1:8085
```
- Risponde `200 OK`? → il problema era solo il Proxy Host, ora risolto
- Ancora "Connection refused" o errore 500? → il problema è nel container stesso (vedi
  `SITO_NON_SI_APRE.md`, punti 1-2), non nel proxy — controlliamo insieme i log di `myband_app`
  e `myband_db`

## 6. Se preferisci il certificato SSL pulito

Il vecchio Proxy Host potrebbe già avere un certificato SSL associato a `myband.it` da quando
puntava a WordPress — va bene, il certificato è legato al dominio non al container di
destinazione, quindi HTTPS dovrebbe continuare a funzionare senza bisogno di richiederne uno
nuovo. Se noti errori sul certificato, nel tab **SSL** del Proxy Host puoi comunque forzarne la
rigenerazione.
