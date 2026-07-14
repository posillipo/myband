# Primo push su GitHub — repository già creato manualmente

Hai già creato il repository vuoto `posillipo/myband` su GitHub. Ecco esattamente cosa fare ora
dal tuo PC Windows, passo per passo.

## 1. Verifica di avere Git installato

Apri **PowerShell** (cerca "PowerShell" nel menu Start) e digita:
```powershell
git --version
```
Se dà errore "non riconosciuto", installa Git da https://git-scm.com/download/win (opzioni di
default vanno bene), poi riapri PowerShell.

## 2. Estrai lo zip che ti ho dato

Estrai `myband-platform.zip` in una cartella a tua scelta, ad esempio:
```
C:\Users\gianluca\Documents\myband
```
Tasto destro sullo zip → "Estrai tutto...". La cartella `.git` (nascosta) deve essere inclusa —
se in Esplora File non la vedi, attiva Visualizza → Elementi nascosti per controllare che ci sia.

## 3. Apri PowerShell dentro quella cartella

Nella cartella appena estratta: tieni premuto **Shift** + click destro nello spazio vuoto →
"Apri finestra PowerShell qui" (o "Apri terminale qui" su Windows 11).

Verifica di essere nel posto giusto:
```powershell
git status
git log --oneline
```
Devi vedere una lista di commit già pronti (non è un progetto vuoto, ha già la cronologia).

## 4. Collega il repository GitHub e fai il push

```powershell
git remote add origin https://github.com/posillipo/myband.git
git push -u origin main
```

## 5. Login

Si aprirà una finestra del browser per il login GitHub (Git Credential Manager) — accedi con il
tuo account e autorizza. La prossima volta non te lo chiederà più.

## 6. Verifica

Ricarica la pagina https://github.com/posillipo/myband nel browser: dovresti vedere tutte le
cartelle (`app/`, `database/`) e i file `.md` delle guide.

---

**Se qualcosa va storto**, incollami esattamente il messaggio di errore che PowerShell mostra
dopo uno di questi comandi, così lo risolviamo insieme.
