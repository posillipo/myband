# Test con l'SMTP di gianlucadipietro.it

Il codice supporta già questa configurazione (porta 25 senza cifratura è una delle opzioni
previste). Ecco i valori esatti da inserire nella pagina **Area Admin → Email / SMTP**:

| Campo (pagina admin) | Valore da inserire |
|---|---|
| Host SMTP | `smtp.gianlucadipietro.it` |
| Porta | `25` |
| Tipo di connessione | **Nessuna cifratura** (corrisponde a `EnableSsl=false`) |
| Username SMTP | `info@gianlucadipietro.it` |
| Password SMTP | `e7cq7fl7` |
| Email mittente (From) | `info@gianlucadipietro.it` |
| Nome mittente | myband.it (o quello che preferisci) |

Salva, poi usa subito il pulsante **"Invia prova"** con una tua email per verificare.

## Se il test fallisce: causa probabile

Il tuo server Hetzner potrebbe avere la **porta 25 in uscita bloccata**. È una misura di
sicurezza molto comune sui provider cloud (Hetzner, AWS, Google Cloud, ecc.) per prevenire l'uso
dei server per inviare spam: bloccano di default le connessioni SMTP dirette sulla porta 25 in
uscita, a prescindere dal dominio di destinazione.

### Come verificarlo

Se hai accesso SSH al server Hetzner, puoi testare la connessione direttamente:
```bash
telnet smtp.gianlucadipietro.it 25
```
- Se si connette e vedi una risposta tipo `220 ...` → la porta non è bloccata, il problema è
  altrove (credenziali, configurazione del server di posta, ecc.) — mandami il log esatto
  dell'errore che vedi nel test invio (in Area Admin → Email/SMTP, o nei log del container
  `myband_app`, cerca righe `[SimpleSmtpMailer]`)
- Se resta in attesa senza rispondere, o dà "Connection timed out" → la porta 25 è bloccata dal
  provider, va richiesta la porta 587 (se il tuo server di posta la supporta) oppure serve
  contattare il supporto Hetzner per lo sblocco (alcuni provider lo sbloccano su richiesta per
  account verificati)

### Alternativa più semplice

Se il tuo hosting che gestisce `gianlucadipietro.it` (mi risulta Aruba da conversazioni
precedenti) offre anche la porta 587 con TLS per la stessa casella `info@gianlucadipietro.it`,
prova quella al posto della 25 — è quasi sempre disponibile e non soggetta agli stessi blocchi.
Controlla nel pannello del tuo hosting le impostazioni SMTP complete (di solito compare sia la
25 che la 587/465).

## Prossimo passo

Prova il test con i valori sopra e mandami esattamente il messaggio di errore che vedi (quello
che appare dopo aver premuto "Invia prova"), così capiamo se è un blocco di porta o altro.
