# Portainer: il pulsante "Deploy the stack" resta disattivato

Quando quel pulsante non si attiva, di solito è perché Portainer sta ancora aspettando che tu
compili un campo obbligatorio, oppure c'è un errore di validazione non troppo visibile. Controlla
in ordine:

## 1. Campo "Name" dello stack
- Deve contenere solo lettere minuscole, numeri e trattini (es. `myband`)
- Niente spazi, maiuscole o caratteri speciali

## 2. Repository URL
- Deve essere l'URL completo con `.git` alla fine:
  ```
  https://github.com/posillipo/myband.git
  ```
- Controlla di non aver lasciato spazi prima/dopo incollando

## 3. Repository reference
- Deve essere valorizzato, es: `refs/heads/main`
- Se lasci questo campo vuoto, alcune versioni di Portainer non attivano il deploy

## 4. Compose path
- Deve essere esattamente: `docker-compose.yml`
- Attenzione a maiuscole/minuscole e a eventuali spazi

## 5. Sezione Authentication (repository privato)
- Se il toggle "Authentication" è **attivo** ma hai lasciato Username o Personal Access Token
  **vuoti**, Portainer blocca il deploy senza sempre mostrare un messaggio chiaro
- Se il repository è privato: compila entrambi i campi
- Se non sei sicuro che sia privato: prova a disattivare il toggle "Authentication" — se il
  repository è pubblico funziona comunque senza credenziali

## 6. Environment variables
- Controlla che ogni riga abbia sia il **Nome** che il **Valore** compilati (una riga a metà,
  con nome inserito ma valore vuoto o viceversa, blocca il pulsante in alcune versioni)
- Le tre che servono:
  ```
  DB_PASSWORD
  DB_ROOT_PASSWORD
  SITE_URL
  ```

## 7. Prova a ricaricare la pagina
A volte è semplicemente un problema dell'interfaccia (bug JS): salva mentalmente i valori inseriti,
premi F5 per ricaricare la pagina di Portainer, e reinserisci i campi da capo.

## 8. Controlla la console del browser
Se dopo tutti i controlli sopra il pulsante resta comunque disattivato:
1. Premi **F12** per aprire gli strumenti sviluppatore del browser
2. Vai sulla scheda **Console**
3. Guarda se compare un errore in rosso
4. Copialo e incollamelo, così capiamo la causa esatta

---

## Errore riscontrato: "Invalid username or token. Password authentication is not supported"

Questo errore conferma che il repository `posillipo/myband` è **privato**, e GitHub non accetta
più la password del tuo account per operazioni Git (clone, push, pull) — serve un **Personal
Access Token (PAT)** al posto della password.

### Genera il token

1. Vai su https://github.com/settings/tokens
2. **Generate new token** → **Generate new token (classic)**
3. **Note**: scrivi qualcosa tipo "Portainer Hetzner myband"
4. **Expiration**: scegli una durata (es. 90 giorni, o "No expiration" se preferisci non doverlo
   rinnovare — meno sicuro ma più comodo)
5. **Scopes**: spunta solo **`repo`** (basta quello, dà accesso in lettura/scrittura ai repository)
6. **Generate token** in fondo alla pagina
7. **Copia subito il token** (inizia con `ghp_...`) — GitHub te lo mostra una sola volta, se lo
   perdi devi generarne uno nuovo

### Usalo in Portainer

Torna sulla schermata "Add stack":
1. Sezione **Authentication**: assicurati che il toggle sia **attivo**
2. **Username**: il tuo username GitHub (es. `posillipo` se è quello del tuo account, altrimenti
   il tuo username personale — non necessariamente uguale al nome dell'organizzazione/repo)
3. **Personal Access Token** (non "Password"): incolla qui il token `ghp_...` appena generato,
   **non** la tua password di GitHub
4. Riprova **Deploy the stack**

### Alternativa più semplice: rendi il repository pubblico

Se il codice non contiene dati sensibili (e non dovrebbe: `.env` è escluso da `.gitignore`, upload
utenti restano sul server e non su Git), puoi evitare tutta la gestione del token rendendo il
repository pubblico:
1. Su GitHub: `posillipo/myband` → **Settings** → in fondo, **Danger Zone** → **Change visibility**
   → **Change to public**
2. In Portainer, disattiva il toggle "Authentication" e riprova il deploy

---

## Errore: "authorization failed: Write access to repository not granted"

Questo errore compare quasi sempre per uno di questi motivi:

### 1. Hai creato un token "Fine-grained" invece di "Classic"
GitHub oggi propone due tipi di token nella pagina Settings → Developer settings:
- **Fine-grained tokens** (più recenti, permessi granulari per repository)
- **Tokens (classic)** (il tipo più semplice e compatibile)

Per Portainer conviene usare il tipo **classic**, che evita problemi di permessi come questo:
1. https://github.com/settings/tokens (nota: URL diverso da `tokens?type=beta`, che sono i
   fine-grained)
2. **Generate new token** → **Generate new token (classic)**
3. Scope: spunta **`repo`** (l'intero blocco, non solo `public_repo`)
4. Genera e copia il nuovo token, sostituiscilo in Portainer

### 2. Hai selezionato lo scope sbagliato
Se nella pagina di creazione del token classic hai spuntato solo `public_repo` invece di
`repo` (l'intero gruppo), GitHub nega l'accesso ai repository privati. Assicurati che sia
spuntato il checkbox principale **`repo`** in alto (che include automaticamente tutti i sotto-scope).

### 3. Se hai usato un token Fine-grained e vuoi tenerlo
Verifica che nella configurazione del token:
- **Resource owner** sia `posillipo` (il proprietario corretto del repository)
- **Repository access** → "Only select repositories" → sia selezionato `myband`
- **Permissions** → **Repository permissions** → **Contents** sia impostato su
  **"Read and write"** (non "Read-only")

### 4. Se "posillipo" è un'organizzazione GitHub (non il tuo account personale)
Le organizzazioni possono richiedere un'approvazione esplicita ("SSO authorization") per ogni
token creato, anche se hai già i permessi corretti:
1. Dopo aver generato il token, su https://github.com/settings/tokens dovresti vedere un
   pulsante **"Enable SSO"** o **"Authorize"** accanto al token appena creato
2. Cliccalo e autorizza l'accesso all'organizzazione `posillipo`

Se non sei sicuro se `posillipo` sia il tuo account personale o un'organizzazione: controlla
l'URL del profilo su GitHub, oppure dimmelo e verifico con te.

### Soluzione più rapida se continui ad avere problemi
Rendi il repository pubblico (Settings → Danger Zone → Change visibility) e disattiva il toggle
"Authentication" in Portainer — elimina il problema alla radice, dato che il codice non contiene
dati sensibili.


