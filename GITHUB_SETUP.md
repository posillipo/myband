# Pubblicare MyBand.it su GitHub

Il progetto è già pronto come repository Git (inizializzato con il primo commit). Ti restano solo
i passaggi per crearlo su GitHub e collegarlo.

## 1. Crea il repository su GitHub

Vai su https://github.com/new e crea un nuovo repository:
- Nome: `myband-platform` (o quello che preferisci)
- Visibilità: **Privato** consigliato, almeno finché il progetto non è pronto per il pubblico
  (contiene la struttura di un'app con dati utenti reali)
- **NON** selezionare "Initialize with README" (il progetto ne ha già uno, eviti conflitti)

Al termine GitHub ti mostrerà un URL tipo:
```
https://github.com/TUO-USERNAME/myband-platform.git
```

## 2. Estrai lo zip in WSL (se non l'hai già fatto per l'ultima versione)

```bash
cd ~
unzip -o myband-platform.zip
cd myband
```

> Nota: lo zip include già la cartella `.git` con la cronologia — non serve rifare `git init`.

## 3. Collega il repository remoto e pubblica

```bash
git remote add origin https://github.com/TUO-USERNAME/myband-platform.git
git push -u origin main
```

Ti verrà chiesta l'autenticazione GitHub. Dal 2021 GitHub non accetta più la password diretta,
serve un **Personal Access Token (PAT)**:

1. Vai su https://github.com/settings/tokens → "Generate new token (classic)"
2. Seleziona lo scope `repo`
3. Copia il token generato (non potrai rivederlo dopo)
4. Quando `git push` chiede la password, incolla il token al posto della password

In alternativa, più comodo per il futuro, configura l'autenticazione SSH:
```bash
ssh-keygen -t ed25519 -C "gianluca@gianlucadipietro.com"
cat ~/.ssh/id_ed25519.pub
```
Copia la chiave pubblica su https://github.com/settings/keys, poi usa l'URL SSH invece di HTTPS:
```bash
git remote set-url origin git@github.com:TUO-USERNAME/myband-platform.git
git push -u origin main
```

## 4. Verifica che il file `.env` NON sia stato caricato

Il `.gitignore` incluso esclude già `.env` (contiene le password del database), ma prima del push
puoi controllare:
```bash
git status
git ls-files | grep .env
```
Deve restituire solo `.env.example`, mai `.env`. Se per errore `.env` risulta tracciato:
```bash
git rm --cached .env
git commit -m "Rimuovo .env dal tracking"
```

## 5. Ad ogni modifica futura

```bash
git add -A
git commit -m "Descrizione della modifica"
git push
```

## 6. Quando sarai pronto per Hetzner

Potrai fare `git clone` direttamente sul server invece di trasferire lo zip manualmente:
```bash
git clone https://github.com/TUO-USERNAME/myband-platform.git
# oppure, con SSH già configurato sul server:
git clone git@github.com:TUO-USERNAME/myband-platform.git
```
Poi solo `cp .env.example .env`, imposti le password e `docker compose up -d --build` come al solito.
