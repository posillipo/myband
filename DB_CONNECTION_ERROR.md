# Errore "getaddrinfo for db failed" — il container app non trova il container db

Buona notizia: Nginx ora raggiunge correttamente il container `myband_app` (altrimenti non
vedresti nemmeno questo errore PHP, vedresti un timeout o un 502). Il problema è un gradino più
sotto: il container `app` non riesce a risolvere il nome `db` sulla rete Docker interna.

Questo succede quasi sempre per uno di questi motivi. Controlla in ordine.

## 1. Il container myband_db esiste ed è "running"?

In Portainer → **Containers**, cerca `myband_db` nell'elenco:

- **Non esiste per niente** → il servizio `db` non è mai partito. Vai al punto 2
- **Esiste ma è in stato "Restarting" o "Exited"** → il container crasha continuamente. Apri i
  suoi **Logs** e guarda l'ultimo errore: incollamelo, di solito è un problema di permessi sul
  volume o una password non valida
- **È "Running"** → allora il problema è di rete, non del container in sé. Vai al punto 3

## 2. Se myband_db non esiste: rivedi lo stack

In Portainer → **Stacks** → apri `myband` → tab **Containers** in basso: dovresti vedere sia
`myband_app` che `myband_db` elencati. Se manca `myband_db`:
- Apri l'**Editor** dello stack e verifica che il blocco `db:` nel docker-compose.yml sia
  presente (non dovrebbe essere stato modificato, ma controlliamo)
- Prova un **redeploy** dello stack (pulsante "Pull and redeploy" o "Update the stack")

## 3. I due container sono sulla stessa rete Docker?

In Portainer → **Networks**, cerca una rete con un nome tipo `myband_myband_net` (Portainer
aggiunge il nome dello stack come prefisso). Aprila e controlla che nella lista "Containers
using this network" compaiano **entrambi**: `myband_app` e `myband_db`.

- Se ne manca uno, è il segno che qualcosa nel deploy non è andato a buon fine per quel servizio
  specifico — prova un redeploy completo dello stack (Stacks → myband → **Down**, poi
  **Deploy the stack** di nuovo, non solo "update")

## 4. Riavvia semplicemente il container app

A volte capita che `db` non sia ancora pronto nell'istante esatto in cui `app` parte (il
`depends_on` nel docker-compose garantisce solo l'ordine di avvio, non che il database sia già
pronto ad accettare connessioni). Prova prima la soluzione più semplice:

In Portainer → **Containers** → `myband_app` → **Restart**

Poi ricarica `https://myband.it`.

## 5. Controlla le variabili d'ambiente dello stack

In Portainer → **Stacks** → `myband` → verifica che siano presenti e compilate correttamente:
```
DB_PASSWORD = ...
DB_ROOT_PASSWORD = ...
SITE_URL = https://myband.it
```
Se manca `DB_PASSWORD`, il container `db` potrebbe non essere nemmeno riuscito a inizializzarsi
correttamente al primo avvio (MySQL richiede una password valida per l'utente applicativo).

## 6. Se niente di questo risolve

Fammi sapere questi tre dati e troviamo la causa esatta insieme:
1. Stato del container `myband_db` (Running / Restarting / Exited) e, se disponibili, le ultime
   10-15 righe dei suoi log
2. Se nella rete Docker dello stack compaiono entrambi i container
3. Il contenuto della sezione "Environment variables" dello stack in Portainer (puoi oscurare i
   valori delle password, mi basta sapere se i nomi delle variabili sono presenti)
