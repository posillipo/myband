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

**Per aiutarti più precisamente**: fammi uno screenshot della schermata "Add stack" così com'è
adesso (con tutti i campi visibili) — vedo subito quale campo manca o è compilato in modo errato.
