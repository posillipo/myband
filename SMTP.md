# Email / SMTP — configurazione e diagnosi

## Configurazione (dall'interfaccia, nessuna variabile d'ambiente da gestire)

**Area Admin → Email / SMTP**: host, porta, tipo di connessione (TLS/SSL/nessuna), username,
password, mittente. Include un pulsante **"Invia prova"** per testare subito senza registrare
account fittizi. Se lasci il campo password vuoto quando salvi, resta quella già impostata.

Le vecchie variabili d'ambiente (`SMTP_HOST` ecc., se già configurate su Portainer) restano
valide come ripiego, ma la pagina admin ha sempre la priorità se compilata.

Se l'SMTP non è configurato, non si rompe nulla: i messaggi di contatto restano salvati in
dashboard, e per gli account nuovi serve la verifica manuale da Area Admin finché non attivi
l'invio automatico.

## Provider consigliato: SendPulse (o altro servizio email transazionale)

Un servizio come SendPulse, Brevo o Mailgun è preferibile a una casella email generica su
hosting condiviso, perché pensato per invio automatico da applicazioni e funziona su porte
standard non bloccate dai provider cloud:
```
Host:     smtp-pulse.com  (o quello indicato dal tuo provider)
Porta:    587
Tipo:     TLS
Username: (login SMTP dedicato generato dal pannello del provider — spesso diverso dall'email
           di accesso all'account)
Password: (generata dal pannello)
```

## Opzione "Verifica il certificato SSL del server"

Disattivala **solo** se il tuo provider usa un hostname personalizzato che non corrisponde al
nome nel certificato (capita con alcuni hosting condivisi, es. Aruba: hostname tipo
`smtp.tuodominio.it` ma certificato intestato al nome reale del provider, es.
`smtps.aruba.it`). In quel caso, prima di disattivare la verifica, prova semplicemente a usare
come Host il nome esatto del certificato — se funziona così, è meglio (nessun compromesso sulla
sicurezza TLS).

## Interpretare gli errori nei log (Portainer → Containers → myband_app → Logs, cerca `[SimpleSmtpMailer]`)

| Errore | Significato |
|---|---|
| `Connessione SMTP fallita: Connection timed out` | La porta è bloccata dal provider (es. Hetzner blocca di default la porta 25 in uscita per prevenire spam — non risolvibile lato codice, serve un'altra porta o servizio) |
| `Connessione SMTP fallita: Connection refused` | Host o porta sbagliati |
| `Risposta SMTP inattesa: atteso 220, ricevuto ''` | Tipo di cifratura sbagliato per quella porta (es. SSL implicito su una porta che si aspetta connessione in chiaro) |
| `Attivazione TLS fallita` | Problema nella fase di cifratura: certificato non corrispondente al nome host (vedi sopra), o il server non supporta STARTTLS su quella porta |
| `Risposta SMTP inattesa: atteso 334/235, ricevuto '530/535 ...'` | Il server rifiuta l'autenticazione (credenziali sbagliate, o richiede prima STARTTLS) |

## Note tecniche

L'invio usa un client SMTP scritto internamente (`app/src/mailer.php`), senza dipendenze esterne
(niente Composer/PHPMailer da gestire nel build Docker). Supporta STARTTLS (porta 587, la più
comune) e SSL implicito (porta 465). L'invio è sempre "best effort": un fallimento non blocca
mai l'esperienza dell'utente (il messaggio di contatto resta comunque salvato, la registrazione
comunque completata).
