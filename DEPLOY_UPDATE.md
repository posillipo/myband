# Aggiornare il sito in produzione con GitHub

Risposta breve: **il codice sì, in automatico. Il database no, quello va gestito a parte.**
Git tiene traccia solo dei file del progetto (PHP, CSS, Dockerfile, ecc.), non dei dati che gli
utenti inseriscono (iscrizioni, link, brani, articoli...). Qui sotto il workflow corretto.

## 1. Cosa viene aggiornato automaticamente con `git pull`

- Tutti i file PHP (pagine, dashboard, area admin)
- CSS/JS
- `Dockerfile`, `docker-compose.yml`
- `database/schema.sql` (ma solo come *file* — vedi punto 3, non si applica da solo)

## 2. Cosa NON viaggia su Git (e va bene così)

- `.env` — le password, resta solo sul server (è nel `.gitignore`)
- I dati nel database — iscrizioni, link, brani caricati, articoli, richieste di contatto
- I file caricati dagli utenti in `app/public/uploads/` — anche questi restano solo sul server

## 3. Il caso critico: modifiche allo schema del database

Quando ti do una modifica che tocca `database/schema.sql` (è successo per la colonna `slug` degli
articoli e per l'area admin), quel file **non si applica da solo** su un database già esistente:
viene eseguito da MySQL solo la primissima volta che il volume del database è vuoto.

Su un sito in produzione **non puoi cancellare il database** come abbiamo fatto in locale
(`docker compose down -v`) — perderesti tutti gli utenti iscritti! In quel caso serve una
**migrazione manuale**: un comando `ALTER TABLE` che aggiunge solo la modifica necessaria,
lasciando intatti i dati esistenti. Te lo preparo io ogni volta che serve, insieme al codice.

## 4. Primo deploy sul server di produzione (una tantum)

```bash
# sul server Hetzner (o dove sarà)
cd /opt
git clone https://github.com/TUO-USERNAME/myband-platform.git myband
cd myband

cp .env.example .env
nano .env   # password vere di produzione, diverse da quelle di test

docker compose up -d --build
```

## 5. Ad ogni aggiornamento successivo del codice

```bash
cd /opt/myband
git pull origin main
docker compose up -d --build
```

Questo:
- Scarica le ultime modifiche al codice da GitHub
- Ricostruisce solo il container `app` con il nuovo codice
- **Non tocca** il volume del database (`db_data`), quindi utenti/dati restano intatti
- **Non tocca** la cartella `uploads/` (foto, brani caricati), è collegata come volume esterno

## 6. Se la modifica include un cambiamento al database

In quel caso il flusso diventa:

```bash
cd /opt/myband
git pull origin main

# applica la migrazione SQL che ti fornisco (esempio):
docker compose exec db mysql -u myband_user -p myband -e "ALTER TABLE ... ;"

docker compose up -d --build
```

Ti darò sempre il comando SQL esatto insieme al codice, così sai cosa lanciare e in che ordine.

## 7. Consiglio: fai un backup prima di ogni aggiornamento importante

```bash
docker compose exec db mysqldump -u root -p myband > backup_$(date +%F_%H%M).sql
```

Un'abitudine di 10 secondi che ti salva la giornata se qualcosa va storto.

## 8. Riepilogo del workflow completo

```
Io ti do il codice aggiornato (e l'eventuale comando SQL di migrazione)
        │
        ▼
Tu fai commit/push sul tuo repository (o applichi le modifiche che ti indico)
        │
        ▼
Sul server: git pull → (eventuale ALTER TABLE) → docker compose up -d --build
        │
        ▼
Sito aggiornato, dati e iscrizioni utenti intatti
```
