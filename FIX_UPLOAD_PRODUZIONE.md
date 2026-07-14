# Errore upload foto/audio in produzione — cartella mancante o permessi errati

## Causa

Il messaggio `Failed to open stream: No such file or directory` indica che dentro il container
`myband_app`, il percorso `/var/www/html/uploads/images/` non esiste davvero (o non è
scrivibile), nonostante nel repository GitHub sia presente con un file `.gitkeep`. Succede
quando il volume montato da Docker sul server non ricrea correttamente le sottocartelle, oppure
le crea ma di proprietà di un utente diverso da quello con cui gira Apache (`www-data`) dentro
il container.

## Soluzione: via GitHub + redeploy (nessuna modifica manuale al container)

Ho aggiunto al progetto uno script di avvio (`app/docker-entrypoint-wrapper.sh`) che sistema
automaticamente questi permessi **ad ogni avvio del container**, quindi il fix è già nel codice:

```bash
mkdir -p /var/www/html/uploads/images /var/www/html/uploads/audio
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads
```

Il `Dockerfile` è stato aggiornato per usarlo come `ENTRYPOINT`, eseguito automaticamente prima
di avviare Apache.

### Passaggi per applicare il fix

1. Fai push del codice aggiornato su GitHub (repo `posillipo/myband`)
2. Su Portainer: **Stacks** → `myband` → **Pull and redeploy** (questo ricostruisce l'immagine
   `app` usando il Dockerfile aggiornato, quindi esegue lo script all'avvio)
3. Riprova il caricamento della foto profilo su `https://www.myband.it/dashboard_profile.php`

Nessun comando da eseguire a mano nel container: il fix è interamente nel codice versionato.

## Perché è successo (per capire se può ripetersi)

Il volume nel `docker-compose.yml` è definito come:
```yaml
volumes:
  - ./app/public/uploads:/var/www/html/uploads
```
Questo è un "bind mount": collega una cartella del server host direttamente dentro il container.
Se quella cartella sull'host non aveva ancora le sottocartelle `images/`/`audio/` con i permessi
corretti al momento del primo avvio (es. per come Portainer ha gestito il clone/deploy iniziale
dal repository Git), il container si ritrova con permessi non coerenti rispetto a quanto previsto
dal `Dockerfile`. Da ora in poi, l'entrypoint automatico corregge questa situazione ad ogni avvio,
quindi il problema non dovrebbe più ripresentarsi anche dopo futuri redeploy.

