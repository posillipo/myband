# Migrazione database — Google Analytics

Aggiunge una sola chiave a `site_settings` per il Measurement ID di Google Analytics 4.

## Comando da eseguire

Stessa procedura delle volte precedenti (Portainer → Containers → `myband_db` → Console →
`mysql -u myband_user -p myband`):

```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('ga_measurement_id', '');
```

## Perché è sicuro

`INSERT IGNORE` è puramente additivo: non tocca nessuna tabella esistente, non modifica dati.
Se la riga esiste già (es. da un deploy precedente), non fa nulla.

## Ordine delle operazioni

1. Push del codice su GitHub
2. Portainer → Pull and redeploy
3. Esegui il comando SQL sopra (una tantum)
4. Vai su **Area Admin → Privacy / Cookie** (la sezione Google Analytics è in fondo alla stessa
   pagina, sotto lo script privacy/cookie)
5. Inserisci il Measurement ID (formato `G-XXXXXXXXXX`, lo trovi in Google Analytics →
   Amministrazione → Flussi di dati → il tuo flusso web)
6. Salva

## Verifica che funzioni

Apri una pagina pubblica (es. `https://www.myband.it/tuoslug`), poi da Google Analytics vai su
**Rapporti → In tempo reale**: dovresti vedere la tua visita comparire entro pochi secondi.
