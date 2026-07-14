# Verifica ora — 3 controlli in Portainer, in quest'ordine esatto

Fai questi 3 controlli e mandami cosa vedi per ciascuno (anche solo uno screenshot per punto va
benissimo). Con queste tre informazioni trovo la causa esatta.

## Controllo 1 — Stato del container myband_db

Portainer → menu laterale **Containers** → cerca `myband_db` nell'elenco.

Guarda la colonna **State/Status**:
- `running` (verde) → scrivimi "running" e passa al controllo 2
- `restarting` o `unhealthy` → clicca sul nome del container → **Logs** → copiami le ultime
  15-20 righe
- Non compare per niente nell'elenco → scrivimelo, vuol dire che non è mai partito

## Controllo 2 — Log del container myband_app (l'errore preciso più recente)

Container `myband_app` → **Logs**. Se l'errore è lo stesso di prima ("getaddrinfo for db
failed"), guarda **quando** è successo l'ultimo (l'orario dell'ultima riga di log): se è vecchio
di minuti/ore rispetto ad ora, prova a ricaricare `https://myband.it` di nuovo per generarne uno
fresco, poi ricontrolla i log.

## Controllo 3 — Rete Docker dello stack

Portainer → menu laterale **Networks** → cerca una rete con un nome che contiene `myband`
(es. `myband_myband_net`).

Aprila e guarda la sezione **Containers** in fondo alla pagina: devono essere elencati
**entrambi** `myband_app` e `myband_db`.
- Se sono elencati entrambi → la rete è a posto, il problema è altrove (torniamo al controllo 1)
- Se manca `myband_db` dalla rete (anche se il container esiste) → trovata la causa: il
  container db non si è mai collegato correttamente alla rete dello stack

---

Mandami il risultato di questi tre controlli (va benissimo anche solo "1: running, 2: stesso
errore di prima orario X, 3: ci sono entrambi") e ti dico esattamente il prossimo passo, senza
tentativi a vuoto.
