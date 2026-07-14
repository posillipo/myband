# Push rapido da Windows (repo già collegato)

Dato che hai già fatto il primo push in passato, da ora in poi ti servono solo questi passaggi
ogni volta che ti do del codice nuovo.

## 1. Scarica ed estrai lo zip aggiornato

Estrai il nuovo `myband-platform.zip` **sopra** la stessa cartella che usi già
(es. `D:\Download\myband-platform\myband`), sostituendo i file. La cartella `.git` è già
presente lì, non serve toccarla.

> Se preferisci lavorare più pulito: estrai in una cartella nuova e poi copia dentro alla tua
> cartella esistente solo i file effettivamente cambiati (te li segnalo io ogni volta).

## 2. Apri PowerShell nella cartella del progetto

Nella cartella del progetto (dove hai già fatto il primo push), Shift + click destro → "Apri
finestra PowerShell qui". Oppure:
```powershell
cd D:\Download\myband-platform\myband
```

## 3. Controlla cosa è cambiato (facoltativo ma utile)

```powershell
git status
```

## 4. Push in 3 comandi

```powershell
git add -A
git commit -m "Descrizione breve della modifica"
git push
```

Non ti chiederà più il login: Windows ha già salvato le credenziali dal primo push.

## 5. Poi su Portainer

Come sempre: **Stacks → myband → Pull and redeploy** per applicare le modifiche in produzione.

---

Questi 3 comandi (`git add -A`, `git commit -m "..."`, `git push`) sono quelli che ripeterai ogni
volta — tienili a portata di mano.

## Se ricevi "fatal: No configured push destination"

Succede quando estrai un nuovo zip **sopra** la cartella esistente: la cartella `.git` che ti do
io non ha il collegamento al tuo repository GitHub configurato (non ho le tue credenziali per
impostarlo dal mio lato). Va ricollegato ogni volta che succede:

```powershell
git remote add origin https://github.com/posillipo/myband.git
git push -u origin main --force
```

Il `--force` è corretto e sicuro qui: sovrascrive la cronologia su GitHub con la tua copia
locale, che contiene comunque tutte le modifiche (comprese quelle fatte direttamente su GitHub,
che io ho già incorporato nei file). Dato che il progetto è solo tuo, non c'è rischio di perdere
il lavoro di altri.

## Come evitare che ricapiti la prossima volta

Da ora in poi, invece di estrarre lo zip intero sopra la cartella esistente, meglio così:
1. Estrai il nuovo zip in una cartella **temporanea** separata (es. `Downloads\myband-new`)
2. Copia **solo i file che ti segnalo come cambiati** dentro alla tua cartella di lavoro
   esistente (quella con il `.git` già configurato), senza toccare la cartella `.git`
3. Procedi normalmente con `git add -A`, `git commit`, `git push`

Così il tuo `.git` locale (con il remote già collegato) resta sempre intatto.
