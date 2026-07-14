# Come recuperare il dettaglio esatto dell'errore SMTP

Il messaggio "Invio fallito" che vedi in Area Admin è generico apposta (per non esporre dettagli
tecnici agli utenti) — il vero motivo dell'errore è scritto nei log del container, serve quello
per capire cosa sta succedendo davvero.

## Come recuperarlo

1. Portainer → **Containers** → clicca su `myband_app`
2. Icona **Logs** (foglio con righe, di solito vicino a "Console")
3. Nella finestra dei log, cerca le righe più recenti che iniziano con:
   ```
   [SimpleSmtpMailer]
   ```
   Di solito è una delle ultime righe, visto che il test l'hai appena fatto
4. Copia il testo completo di quella riga (o le ultime 2-3 righe se ce ne sono più di una) e
   incollamelo in chat

## Se non trovi nulla con quel filtro

Alcune interfacce Portainer permettono di cercare/filtrare testo nei log: cerca una casella di
ricerca in alto nella vista log e digita `SimpleSmtpMailer`. Se proprio non c'è modo di
cercare, scorri le ultime 50-100 righe intorno all'orario in cui hai premuto "Invia prova".

## Cosa mi aspetto di vedere (esempi)

- `Connessione SMTP fallita: Connection timed out (110)` → la porta è bloccata dal
  provider/firewall
- `Connessione SMTP fallita: Connection refused (111)` → l'host o la porta sono sbagliati
- `Risposta SMTP inattesa: atteso 334, ricevuto '530 ...'` → il server rifiuta l'autenticazione
  su questa connessione (spesso vuole prima STARTTLS)
- `Risposta SMTP inattesa: atteso 235, ricevuto '535 ...'` → username o password sbagliati
- `Attivazione TLS fallita` → problema nella fase di cifratura (certificato o versione TLS)

Con il testo esatto capisco subito su quale di questi punti intervenire.
