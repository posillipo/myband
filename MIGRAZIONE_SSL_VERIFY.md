# Migrazione database — opzione verifica certificato SSL

Aggiunge una sola chiave a `site_settings` per poter disattivare la verifica del certificato SSL
quando l'hostname SMTP del tuo hosting non corrisponde al nome nel certificato (il caso che hai
appena riscontrato con Aruba: hostname `smtp.myband.it` ma certificato intestato a
`smtps.aruba.it`).

## Comando da eseguire

```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('smtp_verify_cert', '1');
```

Di default resta attiva (`1`, verifica il certificato) — è la pagina admin che permette di
disattivarla con un checkbox, se necessario per il tuo provider.

## Ordine delle operazioni

1. Push del codice su GitHub
2. Portainer → Pull and redeploy
3. Esegui il comando SQL sopra
4. Vai su **Area Admin → Email / SMTP**
5. **Prova prima senza disattivare nulla** con l'host `smtps.aruba.it` al posto di
   `smtp.myband.it` (il nome esatto del certificato) — se funziona così, meglio: hai HTTPS/TLS
   verificato correttamente, nessun compromesso sulla sicurezza
6. Se preferisci comunque usare `smtp.myband.it`, togli la spunta da "Verifica il certificato
   SSL del server" e salva, poi ripeti il test
