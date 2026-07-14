# Come lanciare il comando SQL per diventare admin

## Su Portainer

1. **Containers** → clicca su `myband_db`
2. Icona **Console** (">_") in alto → **Command**: lascia `/bin/sh` o scegli `/bin/bash` →
   **Connect**
3. Nella console che si apre, accedi a MySQL:
   ```bash
   mysql -u myband_user -p myband
   ```
4. Ti chiede la password: inserisci il valore che hai messo in `DB_PASSWORD` nelle
   Environment variables dello stack (quella che hai scelto tu in fase di deploy)
5. Ora sei dentro MySQL (il prompt cambia in `mysql>`). Esegui, sostituendo con la tua email
   reale di registrazione:
   ```sql
   UPDATE users SET is_admin = 1 WHERE email = 'tua-email-di-registrazione@esempio.it';
   ```
6. Premi Invio. Dovresti vedere qualcosa come `Query OK, 1 row affected`
7. Verifica che sia andato a buon fine:
   ```sql
   SELECT email, is_admin FROM users WHERE email = 'tua-email-di-registrazione@esempio.it';
   ```
   Deve mostrare `is_admin = 1`
8. Esci con:
   ```sql
   exit
   ```

## Dopo il comando

1. Vai su `https://www.myband.it/logout.php` (o clicca "Esci" nel menu)
2. Rifai login con la tua email
3. Dovresti vedere la voce **"Area Admin"** comparire nel menu in alto

Se non compare, ricontrolla di aver usato esattamente la stessa email con cui ti sei registrato
(maiuscole/minuscole non contano per MySQL, ma spazi o refusi sì).
