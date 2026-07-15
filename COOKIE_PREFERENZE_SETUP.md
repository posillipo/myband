# Come far funzionare il pulsante "Preferenze Cookie"

Il pulsante è già collegato nel codice a un comando standard di Iubenda
(`_iub.cs.api.openPreferences()`), che riapre il pannello dove il visitatore può cambiare le
proprie scelte sui cookie. Perché funzioni davvero, servono due cose entrambe da fare su Iubenda
(non nel codice del sito).

## 1. Hai già incollato lo script Iubenda in Area Admin → Privacy / Cookie?

Se il campo "Script privacy / cookie" in quella pagina è ancora vuoto, il pulsante non ha nulla
a cui collegarsi — va prima incollato lo script di embed che Iubenda ti fornisce:

1. Accedi al tuo account su https://www.iubenda.com
2. Se non hai ancora creato una "Cookie Solution" per myband.it, creala (Iubenda ti guida con
   un wizard: tipo di sito, categorie di cookie usate, ecc.)
3. Nella sezione **Cookie Solution → Installazione**, Iubenda ti mostra uno script da incollare
   nel sito — è quello che va copiato nel campo "Script privacy / cookie" in
   **Area Admin → Privacy / Cookie** del tuo pannello myband.it
4. Salva

Da questo momento il banner cookie comparirà sul sito, e la libreria `_iub` sarà caricata
automaticamente su tutte le pagine pubbliche.

## 2. Assicurati che il pulsante "Riapri preferenze" sia abilitato su Iubenda

Nel pannello Iubenda, dentro la configurazione della tua Cookie Solution, cerca l'opzione per
abilitare il **floating button** o il **metodo `openPreferences`** (a seconda della versione
dell'interfaccia Iubenda si chiama leggermente diverso: "Consenti di riaprire le preferenze",
"Preference button", o simile). Di solito è già attivo di default nelle Cookie Solution create
di recente, ma vale la pena controllare.

## 3. Verifica che funzioni

1. Vai su una pagina pubblica del sito (es. `https://www.myband.it/tuoslug`)
2. Dovresti vedere comparire il banner cookie di Iubenda al primo accesso (o in incognito)
3. Chiudi/accetta il banner
4. Scorri in fondo alla pagina e clicca **"Preferenze Cookie"**
5. Deve riaprirsi il pannello delle preferenze cookie

## Se usi un servizio diverso da Iubenda

Se hai scelto Cookiebot, OneTrust o un altro CMP invece di Iubenda, il codice attuale non
funzionerà perché è scritto specificamente per l'API di Iubenda (`_iub.cs.api.openPreferences`).
In quel caso fammelo sapere: ogni servizio ha un comando diverso per riaprire le preferenze
(es. Cookiebot usa `Cookiebot.renew()`), e adatto il codice del pulsante di conseguenza.

## Se il pulsante non fa nulla dopo aver configurato tutto

Apri la Console del browser (F12 → tab "Console") mentre sei sulla pagina pubblica, e clicca il
pulsante "Preferenze Cookie": se compare un errore JavaScript, copialo e mandamelo, così capiamo
se è un problema di script non caricato o di configurazione Iubenda.
