# Analisi dei log SMTP — cosa è successo in ogni tentativo

## Tentativo 1 — 14:41:36: `Risposta SMTP inattesa: atteso 220, ricevuto ''`
Connessione aperta ma nessuna risposta ricevuta dal server (stringa vuota). Di solito capita
quando si sceglie il tipo di cifratura sbagliato per quella porta (es. "SSL implicito" su una
porta che in realtà si aspetta connessione in chiaro, o viceversa) — il server chiude subito la
connessione senza dire nulla perché riceve un protocollo che non si aspetta.

## Tentativo 2 e 3 — 14:43:47 e 14:47:02: `Attivazione TLS fallita`
Qui il dialogo SMTP è iniziato correttamente (saluto ricevuto, comando STARTTLS accettato), ma
la fase di cifratura TLS non si è completata. Le cause più comuni sono due:
- Il certificato del server non corrisponde all'hostname usato per connettersi (lo stesso
  problema già visto con `smtps.aruba.it` vs `smtp.myband.it`)
- Il server su quella porta specifica non supporta davvero STARTTLS, o richiede una versione TLS
  che la libreria PHP non offre di default

## Tentativo 4 — 14:48:44: `Connessione SMTP fallita: Connection timed out (110)`
**Questo è il dato più importante**: un timeout in fase di connessione (non di risposta) è la
firma classica del **blocco della porta 25 in uscita da parte di Hetzner**, esattamente il
sospetto che avevo segnalato all'inizio. Hetzner (come la maggior parte dei provider cloud)
blocca di default le connessioni SMTP dirette sulla porta 25, per prevenire l'uso dei server per
spam. Non è risolvibile lato codice: servirebbe una richiesta di sblocco al supporto Hetzner
(non sempre concessa), oppure evitare del tutto la porta 25.

## Conclusione

Il server SMTP di gianlucadipietro.it/Aruba, così come configurato (porta 25, nessuna
cifratura), **non è utilizzabile da un server Hetzner** per il blocco di rete. Il tentativo con
TLS su porta diversa ha altri problemi (certificato o supporto STARTTLS) che richiederebbero
ulteriori tentativi alla cieca, senza garanzia di successo, perché non conosciamo con certezza se
quel server di posta supporta davvero connessioni cifrate su altre porte.

## Raccomandazione

A questo punto ha più senso completare la configurazione con **SendPulse**, il servizio che
avevi già scelto all'inizio proprio per questo scopo (email transazionali). SendPulse è pensato
per funzionare su porte standard non bloccate (587 con TLS) ed è progettato per l'invio
automatico da applicazioni, a differenza di una casella email generica su hosting condiviso.

Mi servono le credenziali SMTP di SendPulse per procedere:
- **SMTP Host** (di solito `smtp-pulse.com`)
- **SMTP Port** (587)
- **SMTP Login** (attenzione: SendPulse genera un login SMTP dedicato, spesso diverso dalla tua
  email di accesso al pannello)
- **SMTP Password** (anche questa generata da SendPulse)

Le trovi in SendPulse → Impostazioni → SMTP (o "Email API" a seconda della versione
dell'interfaccia). Inseriscile in **Area Admin → Email / SMTP** con porta 587 e tipo di
connessione TLS, poi usa "Invia prova" — dovrebbe funzionare al primo colpo, senza i problemi di
blocco porta o certificato incontrati con Aruba.
