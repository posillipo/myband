# Non sai qual è DB_PASSWORD? Ecco come recuperarla

## 1. Controlla le Environment Variables dello stack su Portainer

1. Portainer → **Stacks** → clicca su `myband`
2. Scorri fino alla sezione **Environment variables** (o apri **Editor** in alto)
3. Cerca la riga `DB_PASSWORD` — il valore dovrebbe essere visibile lì (alcune versioni di
   Portainer mascherano il campo con dei puntini: clicca l'icona dell'occhio 👁 accanto al
   campo per rivelarlo, se presente)

## 2. Se il campo è vuoto o non l'hai mai impostato

Guarda il `docker-compose.yml`: c'è un valore di default già previsto se `DB_PASSWORD` non è
stato configurato:
```yaml
DB_PASS=${DB_PASSWORD:-cambiami_123}
```
Se non hai mai aggiunto `DB_PASSWORD` tra le Environment variables dello stack, la password in
uso è semplicemente:
```
cambiami_123
```
(il valore letterale dopo i due punti, senza i simboli `${...:-}`)

## 3. Verifica rapida

Prova a fare il login MySQL con quel valore:
```bash
mysql -u myband_user -p myband
```
Quando chiede la password, prova prima `cambiami_123`. Se funziona, sei dentro.

## 4. Consiglio per il futuro

Se stai usando ancora la password di default `cambiami_123`, ti consiglio di impostarne una vera
tra le Environment variables dello stack (è quella che protegge il database in produzione).
Quando la cambi lì, ricordati che serve poi un **redeploy dello stack** perché il nuovo valore
venga effettivamente usato dal container.
