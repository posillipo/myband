# Deploy completato — myband.it è online

## Stato confermato (verificato dall'esterno)

`https://www.myband.it` risponde correttamente con la landing page completa:
- Titolo e meta corretti
- Pulsanti "Accedi" e "Registrati" presenti e funzionanti come link
- Le 4 sezioni descrittive (player audio, link, concerti, contatti) renderizzate correttamente
- Nessun errore PHP visibile (a differenza di prima, quando appariva l'eccezione PDO)

Questo conferma che:
- Nginx Proxy Manager instrada correttamente il dominio verso il container `myband_app`
- Il container `myband_app` è raggiungibile e risponde
- La connessione al database (`myband_db`) ora funziona — il problema precedente
  ("getaddrinfo for db failed") si è risolto, probabilmente con il riavvio del container o con il
  tempo necessario perché il database completasse l'inizializzazione

## Prossimi test consigliati, in ordine

1. **Registrazione**: vai su `https://www.myband.it/register.php` e crea il tuo account
   musicista reale (quello che poi promuoverai ad admin)
2. **Promozione ad admin**: stesso comando SQL di prima, mysqldump/mysql via console del
   container `myband_db` in Portainer:
   ```sql
   UPDATE users SET is_admin = 1 WHERE email = 'tua-email-reale@dominio.it';
   ```
3. **Login** e verifica che compaia il link "Area Admin" nel menu
4. **Test upload**: carica una foto profilo e un brano audio dalla dashboard, verifica che si
   vedano nella pagina pubblica (conferma che il volume `uploads/` funzioni correttamente anche
   in produzione)
5. **Test HTTPS**: controlla che il lucchetto sia verde/valido sul dominio (certificato Let's
   Encrypt attivo)
6. **Test pagina pubblica completa**: apri `https://www.myband.it/tuoslug`, `.../tuoslug/blog`
   e `.../tuoslug/contatti` per verificare che tutte le route funzionino in produzione
7. **Test area privacy**: da Area Admin → Privacy/Cookie, incolla lo script Iubenda (se già
   pronto) e verifica che compaia sul sito pubblico

## Nota su www vs senza www

Hai testato `https://www.myband.it` (con www). Verifica anche che `https://myband.it` (senza
www) funzioni allo stesso modo — nel Proxy Host di Nginx Proxy Manager dovresti avere entrambi
i domini configurati (`myband.it` e `www.myband.it`) sullo stesso host, con certificato valido
per entrambi.
