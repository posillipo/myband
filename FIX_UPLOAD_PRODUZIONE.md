# Errore upload foto/audio in produzione — cartella mancante o permessi errati

## Causa

Il messaggio `Failed to open stream: No such file or directory` indica che dentro il container
`myband_app`, il percorso `/var/www/html/uploads/images/` non esiste davvero (o non è
scrivibile), nonostante nel repository GitHub sia presente con un file `.gitkeep`. Succede
quando il volume montato da Docker sul server non ricrea correttamente le sottocartelle, oppure
le crea ma di proprietà di un utente diverso da quello con cui gira Apache (`www-data`) dentro
il container.

## Soluzione: crea le cartelle e sistema i permessi direttamente nel container

In Portainer:
1. **Containers** → clicca su `myband_app`
2. Icona **Console** (">_") in alto → **Command**: scegli `/bin/bash` (se non disponibile,
   prova `/bin/sh`) → **Connect**
3. Nella console che si apre, esegui questi comandi uno alla volta:

```bash
mkdir -p /var/www/html/uploads/images /var/www/html/uploads/audio
chown -R www-data:www-data /var/www/html/uploads
chmod -R 775 /var/www/html/uploads
```

4. Verifica che sia andato a buon fine:
```bash
ls -la /var/www/html/uploads/
```
Dovresti vedere `images` e `audio` con proprietario `www-data www-data`.

## Prova di nuovo

Torna su `https://www.myband.it/dashboard_profile.php` e riprova a caricare la foto profilo.
Dovrebbe funzionare senza warning questa volta.

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
dal `Dockerfile`. Il comando `chown`/`chmod` eseguito manualmente ora risolve il problema in modo
permanente: modificando i permessi da dentro il container, li stai modificando anche sul
filesystem reale del server (è la stessa cartella, condivisa tramite il bind mount), quindi
resterà corretto anche dopo un riavvio o un redeploy dello stack.

## Se il problema si ripresenta dopo un futuro "Pull and redeploy"

Se in futuro rifacendo un redeploy dello stack il problema tornasse, fammelo sapere: possiamo
aggiungere un piccolo script di avvio (`entrypoint`) nel Dockerfile che sistema automaticamente
i permessi della cartella uploads ad ogni avvio del container, così non servirà più intervenire
a mano.
