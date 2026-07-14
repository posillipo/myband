# Verifica manuale del tuo account via SQL

Nella stessa console MySQL di prima (`mysql -u myband_user -p myband`), esegui:

```sql
UPDATE users SET email_verified = 1, verification_token = NULL, verification_expires = NULL
WHERE email = 'gianlucadipietro@gmail.com';
```

Verifica che sia andato a buon fine:

```sql
SELECT email, email_verified FROM users WHERE email = 'gianlucadipietro@gmail.com';
```

Deve mostrare `email_verified = 1`.

Poi esci con `exit` e prova il login su `https://www.myband.it/login.php`.
