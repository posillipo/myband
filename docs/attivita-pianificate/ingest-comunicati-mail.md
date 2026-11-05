# Attività pianificata: ingest comunicati da email → timeline myband.it

Stato: **mattone 1 — triage in sola lettura (dry-run)**. Non pubblica nulla.

## Perché

Gran parte dei contenuti della timeline di myband.it nasce da email inviate da band, uffici stampa
e giornalisti (uscite discografiche, date di concerti). Oggi il passaggio email → sito esiste già
ma è manuale/semi-automatico. Questa attività lo rende ripetibile, controllabile e senza doppioni.

## Come funziona oggi (osservato su Gmail e sul profilo MyBand, 5/11/2026)

- I comunicati arrivano su `info@my-band.it` e sono inoltrati su Gmail.
- Mittenti ricorrenti: BIG TIME Ufficio Stampa (`pressoff@bigtimeweb.it`), Ender Comunicazione,
  HF4, Almae Music (`mediasender.it`), Sorry Mom!. Etichette già usate: `MYBAND`,
  `MYBAND/BigTime`, `MYBAND/MyBandTv Appuntamenti`.
- Una mail già lavorata riceve l'etichetta **`MyBand-elaborata`**. Questo è il marcatore di
  idempotenza da riusare, non va inventato un secondo sistema.
- Il sito pubblica poi un evento (`create_event`, con "Fonte: <url articolo blog>" nella
  descrizione) e/o un articolo blog, e manda una mail di notifica "myBand.it ha pubblicato…".
- Rumore da ignorare nella stessa casella: Pinterest, notifiche di registrazione/follow di
  myband, newsletter commerciali (viaggi, DICE, Vivaticket, Bandsintown), mail di sistema
  inviate da `info@my-band.it` stesso.

## Perimetro del mattone 1

Solo leggere e produrre un **rapporto**. Zero scritture su myband.it, zero etichette su Gmail,
zero invii. Serve a validare le regole sui dati reali prima di dare il permesso di pubblicare.

### 1. Selezione candidate

```
in:inbox newer_than:3d -label:MyBand-elaborata -from:info@my-band.it
  (to:info@my-band.it OR deliveredto:info@my-band.it)
```

Poi scarto per mittente: lista di esclusione (Pinterest, trivago, ecc.) mantenuta qui sotto.
Lettura thread sempre con `messageFormat: PLAIN_TEXT` (i comunicati pesano 70 KB+ per via di
biografie e link di tracking).

### 2. Classificazione

Ogni mail ha **una classe principale** (obbligatoria) e può avere **classi secondarie**
(una presentazione d'album con date live è `uscita` + `concerto`).

| Classe | Segnali | Esempi reali in casella | Destinazione futura |
|---|---|---|---|
| `uscita` | "esce il", nuovo singolo/album/EP/video, radio date, pre-save, ISRC, label copy | Pier Cortese "Come noi" (BIG TIME), Ivan Comar "Milioni di Dubbi" (Almae) | post timeline; articolo blog se c'è comunicato esteso |
| `concerto` | data + luogo + live, "in concerto", tour, presentazione dal vivo | Fanali (presentazione mini album), Love Bombing tour | evento, una riga per data |
| `festival_rassegna` | programma multi-giorno o multi-artista, cartellone | Monferr'Autore Festival, MEI Faenza | evento per ogni appuntamento citato + articolo blog di contesto |
| `teatro` | spettacolo, regia, compagnia, teatro come luogo, "in scena" | Il medico dei pazzi (Teatro Cilea), L'Ospedale dei Giocattoli Rotti (Teatro San Giustino) | evento, etichettato teatro |
| `incontro_attivita` | presentazione libro, laboratorio, incontro, proiezione, masterclass | rassegne tipo "Fiori nei giardini chiusi" | evento o post, secondo se ha data/luogo fissi |
| `notizia` | fatto senza data utile: premio, annuncio, intervista, video | comunicati di sola novità | post timeline o articolo blog |
| `altro_da_valutare` | pertinente alla musica/spettacolo ma non riconducibile alle precedenti | — | niente: finisce nel rapporto per decisione umana |
| `scarta` | promo commerciale, notifica di sistema, nessun fatto | Pinterest, newsletter viaggi, mail di sistema di myband | niente |

Regole di scelta:
- Se c'è una **data con luogo**, vince la logica evento (anche per teatro e incontri), perché
  così fa già il sito: gli spettacoli teatrali sono oggi eventi.
- Se la data è solo l'uscita di un brano (radio date), è `uscita`, non evento.
- `create_event` non ha un campo categoria: il tipo si segna in testa alla descrizione
  (es. "Teatro · …") e nell'hashtag/tag dell'articolo. **Da decidere** se myband.it ha o vuole
  una categoria vera, altrimenti il tipo si perde nel testo.
- Un mittente non determina la classe: lo stesso ufficio stampa manda uscite, concerti e teatro.

### 3. Estrazione (un record per uscita / per singola data)

Campi: `classe`, `artista`, `titolo`, `data` (ISO 8601 con fuso Europe/Rome), `luogo`, `città`,
`provincia`, `link_biglietti`, `link_ascolto/pre-save`, `immagine` (fonte scelta come in 3b: allegato / inline /
link press kit / da generare, più nome file o URL), `fonte` (id thread Gmail + mittente), `confidenza`
(alta/media/bassa), `da_confermare` (es. orario mancante → il sito usa "Orario da confermare").

Una tournée con N date produce **N record**, uno per data. Esempio reale: eventi 182 e 183
sono lo stesso spettacolo in due serate diverse e sono corretti entrambi.

### 3b. Immagini (stesso ordine di priorità, sempre)

Per ogni record si sceglie **una sola fonte**, nell'ordine:

1. **Allegato** alla mail: `mimeType` `image/*` (jpg/png/webp). Si ignorano `.docx`, `.pdf`,
   file minuscoli, loghi, firme email, icone social. Più allegati: si preferisce la foto
   più grande e non il logo; il nome file aiuta ("Foto di …", "cover", "locandina").
2. **Inline** nel corpo HTML, solo se non c'è nessun allegato valido: tag `<img>` con URL
   pubblico stabile o `cid:`. Si scartano pixel di tracciamento (1×1), icone, banner di
   firma, immagini dei pulsanti "social".
3. **Link a press kit / foto HD** (Drive, Dropbox, WeTransfer): non si scaricano da soli, si
   segnala nel rapporto come `immagine: link press kit` perché decida una persona.
4. **Generata da zero**: solo se 1, 2 e 3 non danno nulla. Il rapporto la marca
   `immagine: DA GENERARE` e, quando verrà pubblicata, `generata` (mai spacciata per foto
   dell'artista, niente volti di persone reali o riproduzioni di loghi).

Mai immagini prese dal web o da altri siti: solo ciò che è nella mail.

Formati di uscita (si ritaglia con "cover" centrato sul soggetto, mai deformando):
- **Post social / copertine**: **4:5**, 1080×1350 px.
- **Storie**: **da confermare** (proposta: 9:16, 1080×1920 px). Va nel campo
  `image_story_url` della coda social.
Se la sorgente è più piccola del formato richiesto si segnala `bassa risoluzione`.

**Limite tecnico noto (da risolvere nel mattone 2).** Con gli strumenti Gmail oggi
disponibili si vedono nome, tipo e id degli allegati, ma non c'è un modo di scaricarne il
contenuto per ricaricarlo su myband (`request_image_upload` vuole il file). Il mattone 1
si limita quindi a **rilevare** quale immagine c'è; il recupero vero va risolto prima di
pubblicare. Le immagini inline con URL pubblico, invece, si possono scaricare e ricaricare.

### 4. Dedupe

Chiave eventi: `normalizza(artista|titolo) + data(giorno) + normalizza(luogo)`.
Chiave uscite: `normalizza(artista) + normalizza(titolo brano) + data uscita`.

Il confronto avviene contro `list_events` (finestra ±1 giorno sulla data) e `list_social_posts`
/ `list_blog_posts` recenti di profilo `MyBand`. Il titolo da solo **non** basta: la stessa
produzione in date diverse non è un doppione. Match esatto → `gia_presente`; titolo uguale ma
data o luogo diversi → `nuova_data`; parziale → `da_verificare` (decide una persona).

### 5. Output

Un rapporto breve in chat (e nel riepilogo della sessione pianificata), una riga per record:
`classe | artista – titolo | data | luogo | esito dedupe | confidenza | thread`.
Più il conteggio finale: candidate lette, scartate, nuove, già presenti, da verificare.

## Cosa NON fa (volutamente, per i mattoni successivi)

- Non pubblica (mattone 2: pubblicazione in `draft`/`scheduled`, mai `published` diretto).
- Non applica `MyBand-elaborata` (mattone 2, solo dopo pubblicazione riuscita).
- Non accoda su Metricool (`enqueue_social_queue`) (mattone 3).
- Non risponde ai mittenti.

## Lista di esclusione mittenti (da far crescere)

`recommendations@*.pinterest.com`, `*@trivago.com`, `newsletter@alpitourworld-digital.com`,
`lastminute@yallayalla.it`, `notifications@hello.bandsintown.com`, `news@communication.vivaticket.com`,
`gigs@mailer.dice.fm`, `googlealerts-noreply@google.com`, `info@my-band.it`.

## Pianificazione

Proposta: ogni mattina feriale, fuori dall'ora di punta, finestra `newer_than:3d` (la
sovrapposizione è sicura perché la dedupe è deterministica). Frequenza e orario da confermare.
