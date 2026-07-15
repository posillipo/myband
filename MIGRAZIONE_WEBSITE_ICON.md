# Migrazione database — flag "sito web personale" sui link

Aggiunge una colonna alla tabella `links` per marcare manualmente un link come sito web
personale del band manager (mostrato come icona globo, dato che un sito personale può avere
qualsiasi dominio e non è riconoscibile automaticamente).

## Comando da eseguire

Portainer → Containers → `myband_db` → Console → `mysql -u myband_user -p myband`:

```sql
ALTER TABLE links ADD COLUMN is_website_icon TINYINT(1) NOT NULL DEFAULT 0;
```

## Perché è sicuro

Aggiunge solo una colonna con default `0` (non selezionato): tutti i link esistenti restano
invariati, nessuno diventa improvvisamente un'icona "sito web" senza che il band manager lo
scelga esplicitamente dal form.

## Ordine delle operazioni

1. Push del codice su GitHub
2. Portainer → Pull and redeploy
3. Esegui il comando SQL sopra (una tantum)
4. Testa: Dashboard → Link → aggiungi un link, spunta "È il tuo sito web personale?", salva,
   verifica che compaia come icona globo in cima alla pagina pubblica invece che come pulsante
