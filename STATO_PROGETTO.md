# Stato del progetto MyBand.it — riepilogo

## ✅ Fatto e in produzione

### Piattaforma base
- Registrazione/login musicisti, dashboard con Profilo/Link/Brani/Eventi/Blog/Contatti
- Pagina pubblica per ogni artista (`myband.it/tuoslug`)
- Upload foto profilo e brani audio (con fix permessi via entrypoint automatico)

### Pagine pubbliche riorganizzate
- Header condiviso identico su tutte le pagine: avatar + nome + menu di navigazione
- Menu: **Home | Blog | Brani | Eventi | Contatti**, ognuna su URL dedicata:
  - `/tuoslug` (home/linktree)
  - `/tuoslug/blog` + singolo articolo con permalink SEO (`/blog/anno.mese.giorno.slug`)
  - `/tuoslug/brani`
  - `/tuoslug/eventi`
  - `/tuoslug/contatti`
- Tema "colorful" ispirato al layout Meeek (sfondo sfumato, icone social riconosciute
  automaticamente, pulsanti pastello per gli altri link)
- Meta tag Open Graph/Twitter Card su ogni pagina (condivisibili sui social)

### Area amministratore
- Ruolo admin (tu, ora attivo ✅)
- Elenco utenti iscritti con conteggi (link/brani/eventi/post/contatti)
- Dettaglio singolo utente + richieste di contatto ricevute
- Attiva/disattiva account
- **Rendi admin / Rimuovi admin** per altri utenti (appena aggiunto)
- Pagina Privacy/Cookie: incolli lo script Iubenda, va in automatico su tutte le pagine pubbliche

### Notifiche email
- Client SMTP autonomo (nessuna libreria esterna)
- Notifica email al musicista quando riceve un messaggio da `/tuoslug/contatti`
- **In attesa delle credenziali SendPulse** per attivarlo davvero (per ora l'SMTP non è
  configurato, quindi le notifiche sono "silenziosamente" disattivate — nessun errore, ma nessuna
  email arriva ancora)

### Infrastruttura
- Docker (PHP 8.2 + Apache + MySQL 8), tutto in `docker-compose.yml`
- Repository GitHub `posillipo/myband`, deploy tramite Portainer collegato al repo
- Regola stabilita: ogni modifica passa da GitHub + redeploy, mai interventi manuali nei
  container (fatta eccezione per comandi SQL sui *dati*, non sul codice)

---

## ⏳ Deciso ma non ancora implementato

Dalla conversazione sulla dashboard admin completa, resta da fare:

1. **Verifica email alla registrazione** (hai confermato: account bloccato finché non verificato)
   — richiede una modifica al database (2 colonne nuove su `users`) + integrazione con SendPulse
2. **Filtri sull'elenco utenti** admin (per nome/email/stato/data)
3. **Modifica dati utente** dall'area admin (nome, email, slug)
4. **Eliminazione account** dall'area admin (con conferma)
5. **Moderazione contenuti**: eliminare un singolo link/brano/evento/articolo di un utente senza
   disattivare l'intero account
6. **Reinvio email di conferma** per un utente non ancora verificato
7. **Pulsante "invia email di prova"** nella pagina impostazioni SMTP
8. **Casella di contatto globale** (tutte le richieste di tutti gli utenti in un unico posto)
9. **Dashboard overview**: statistiche generali (iscritti totali, attivi, ultimi 7/30 giorni)
10. **Pagina impostazioni GTM / Pixel Facebook** (stesso meccanismo dello script privacy)
11. **Configurazione SendPulse effettiva**: mi servono ancora le credenziali SMTP (host, porta,
    login, password) per attivare davvero le notifiche già scritte nel codice

---

## Prossimo passo

Dimmi da dove vuoi ripartire: possiamo procedere punto per punto dalla lista sopra, nell'ordine
che preferisci, oppure chiudere prima il thread SendPulse (mi servono le credenziali) per avere
subito le notifiche email funzionanti.
