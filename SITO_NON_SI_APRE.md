# Il container è attivo ma il sito non si apre — checklist diagnostica

"Container attivo" significa solo che il processo dentro Docker sta girando, non che sia
raggiungibile dall'esterno correttamente. Segui i controlli in ordine, dal più vicino al
container fino al più esterno (DNS).

## 1. Il container app risponde internamente?

Sul server Hetzner (via SSH, o dalla console del container in Portainer):
```bash
curl -I http://127.0.0.1:8085
```
- **Se risponde con `HTTP/1.1 200 OK`** → il container PHP/Apache funziona, il problema è più
  avanti nella catena (Nginx Proxy Manager o DNS) → vai al punto 3
- **Se dà "Connection refused"** → il container non sta ascoltando sulla porta 8085 → vai al
  punto 2
- **Se dà un errore 500** → il codice PHP sta fallendo (probabilmente la connessione al
  database) → vai al punto 2b

## 2. Controlla i log del container app

In Portainer: container `myband_app` → icona dei log (foglio con righe).
Cerca errori tipo:
- `SQLSTATE[HY000] [2002] Connection refused` → il container `db` non è pronto o le credenziali
  non combaciano
- Errori PHP fatali → incollameli, li leggo

## 2b. Controlla i log del container database

Container `myband_db` → log. Verifica che dica qualcosa come
`ready for connections` verso la fine — se invece vedi errori, il volume del database potrebbe
essersi inizializzato in modo incompleto (capita se il primo avvio è stato interrotto).

Verifica anche che le variabili d'ambiente dello stack (`DB_PASSWORD`, `DB_ROOT_PASSWORD`) siano
esattamente le stesse sia per il servizio `app` che per il servizio `db` — in Portainer, apri lo
stack → **Editor** e controlla la sezione Environment variables.

## 3. Il Proxy Host su Nginx Proxy Manager esiste ed è corretto?

Apri Nginx Proxy Manager (porta 81):
- **Proxy Hosts** → deve esistere una voce per `myband.it`
- **Forward Hostname/IP**: deve essere lo stesso valore usato dagli altri tuoi Proxy Host
- **Forward Port**: deve essere `8085` (o la porta che hai effettivamente scelto — controlla che
  coincida con quella pubblicata nel docker-compose.yml)
- Se il Proxy Host non esiste ancora: è probabilmente questa la causa principale, vai alla
  sezione 4 di `PORTAINER_DEPLOY.md` e crealo

## 4. Il DNS punta al server?

Da qualsiasi PC (anche il tuo):
```bash
nslookup myband.it
```
oppure da WSL:
```bash
dig +short myband.it
```
Deve restituire l'IP del tuo server Hetzner. Se restituisce un altro IP, o niente, il DNS non è
ancora configurato o non si è ancora propagato (può richiedere da pochi minuti a qualche ora dopo
aver impostato il record A).

## 5. Certificato SSL

Se hai già configurato il certificato in Nginx Proxy Manager ma il DNS non era ancora propagato
in quel momento, la richiesta del certificato Let's Encrypt potrebbe essere fallita silenziosamente.
Controlla in Nginx Proxy Manager, tab SSL del Proxy Host, che dica "Certificate valid" e non un
errore.

## 6. Che errore vedi esattamente nel browser?

Per aiutarti più precisamente mi serve sapere **cosa succede esattamente** quando provi ad aprire
il sito:
- La pagina resta a caricare all'infinito e poi va in timeout?
- Vedi un errore "502 Bad Gateway"?
- Vedi un errore "Impossibile raggiungere questo sito" / "DNS_PROBE_FINISHED"?
- Vedi una pagina bianca?
- Vedi un errore PHP visibile?

Dimmi anche **da dove** stai provando ad aprirlo: `https://myband.it`, oppure l'IP diretto del
server con la porta (`http://IP:8085`)?
