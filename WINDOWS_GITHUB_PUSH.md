# Pubblicare MyBand.it su GitHub da Windows

Lo zip che hai scaricato contiene già la cartella `.git` con la cronologia dei commit — non
serve rifare `git init`, ti bastano pochi passaggi da Windows.

## 1. Installa Git per Windows (se non l'hai già)

Se digitando `git --version` nel Prompt dei comandi o PowerShell ottieni un errore, scarica e
installa Git da: https://git-scm.com/download/win (durante l'installazione puoi lasciare tutte
le opzioni di default).

Dopo l'installazione, apri un nuovo **PowerShell** o **Git Bash** (installato insieme a Git) per
avere i comandi disponibili.

## 2. Estrai lo zip

Estrai `myband-platform.zip` in una cartella a tua scelta, ad esempio:
```
C:\Users\gianluca\Documents\myband
```
(tasto destro sullo zip → "Estrai tutto...", oppure doppio click se Windows lo apre come cartella
navigabile e trascina il contenuto fuori)

> Attenzione: assicurati che la cartella `.git` (nascosta) sia stata estratta insieme al resto.
> Se il tuo estrattore nasconde i file/cartelle che iniziano con il punto, in Esplora File attiva
> Visualizza → Elementi nascosti per verificarne la presenza.

## 3. Apri PowerShell nella cartella del progetto

Nella cartella estratta, tieni premuto **Shift** e clicca destro nello spazio vuoto → "Apri
finestra PowerShell qui" (oppure "Apri finestra dei comandi qui" a seconda della versione di
Windows). In alternativa, apri PowerShell e naviga con `cd`:
```powershell
cd C:\Users\gianluca\Documents\myband
```

Verifica che il repository sia riconosciuto:
```powershell
git status
git log --oneline
```
Dovresti vedere i commit già fatti in precedenza.

## 4. Crea il repository vuoto su GitHub (se non l'hai già fatto)

Vai su https://github.com/new:
- Nome: `myband-platform`
- Visibilità: **Privato** consigliato
- **NON** spuntare "Initialize with README" (eviti conflitti con la cronologia già presente)

Copia l'URL che GitHub ti mostra, tipo:
```
https://github.com/posillipo/myband.git
```

## 5. Collega il remote e pubblica

```powershell
git remote add origin https://github.com/posillipo/myband.git
git push -u origin main
```

Se il remote `origin` esiste già da un tentativo precedente e dà errore "remote origin already
exists", usa invece:
```powershell
git remote set-url origin https://github.com/posillipo/myband.git
git push -u origin main
```

## 6. Autenticazione

Windows aprirà una finestra di login GitHub (Git Credential Manager, incluso nell'installazione
di Git per Windows) — accedi con il tuo account e autorizza. Non dovrai reinserire le credenziali
nei push successivi, restano salvate in modo sicuro da Windows.

Se preferisci non usare il login via browser, puoi generare un **Personal Access Token** e
usarlo al posto della password quando richiesto:
1. https://github.com/settings/tokens → "Generate new token (classic)"
2. Scope: `repo`
3. Incolla il token quando Git chiede la password

## 7. Verifica

Ricarica la pagina del repository su github.com: dovresti vedere tutti i file del progetto
(cartelle `app/`, `database/`, i file `.md` delle guide, ecc.) e i 6 commit nella cronologia.

## 8. Controllo di sicurezza: il file .env NON deve essere su GitHub

```powershell
git ls-files | Select-String ".env"
```
Deve restituire solo `.env.example`. Se compare `.env` (senza `.example`), rimuovilo subito:
```powershell
git rm --cached .env
git commit -m "Rimuovo .env dal tracking"
git push
```

## 9. Aggiornamenti futuri da Windows

Ogni volta che ti passo del codice nuovo, ripeti in questa stessa cartella:
```powershell
git add -A
git commit -m "Descrizione della modifica"
git push
```

Da qui in poi, su Hetzner via Portainer basterà il pulsante **"Pull and redeploy"** (vedi
`PORTAINER_DEPLOY.md`) per applicare l'aggiornamento.
