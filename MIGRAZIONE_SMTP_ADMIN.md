# Migrazione database — configurazione SMTP da admin

Aggiunge le chiavi per la configurazione SMTP gestibile dall'area admin. Nessuna colonna nuova
sulle tabelle esistenti, solo nuove righe in `site_settings` (tabella già esistente).

## Comando da eseguire

Stessa procedura delle volte precedenti (Portainer → Containers → `myband_db` → Console →
`mysql -u myband_user -p myband`):

```sql
INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES
  ('smtp_host', ''),
  ('smtp_port', '587'),
  ('smtp_user', ''),
  ('smtp_pass', ''),
  ('smtp_secure', 'tls'),
  ('smtp_from', ''),
  ('smtp_from_name', 'myband.it');
```

## Perché è sicuro

`INSERT IGNORE` non tocca nulla se le righe esistono già, e non modifica nessuna tabella
esistente — è puramente additivo. Se avevi già configurato l'SMTP tramite variabili d'ambiente
su Portainer, quelle continuano a funzionare finché non compili gli stessi campi nella pagina
admin (che a quel punto avrà la priorità).

## Ordine delle operazioni

1. Push del codice su GitHub
2. Portainer → Pull and redeploy
3. Esegui il comando SQL sopra (una tantum)
4. Vai su **Area Admin → Email / SMTP**, inserisci le credenziali SendPulse, salva
5. Usa il pulsante "Invia prova" per verificare che funzioni
