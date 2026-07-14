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
