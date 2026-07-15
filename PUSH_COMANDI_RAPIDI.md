# Comandi PowerShell per aggiornare GitHub

Nella cartella del progetto su Windows (PowerShell):

```powershell
git add -A
git commit -m "Google Analytics + AdminLTE"
git push
```

Se per caso ricevi "fatal: No configured push destination" (capita quando estrai un nuovo zip
sopra la cartella esistente), prima ripeti:
```powershell
git remote add origin https://github.com/posillipo/myband.git
git push -u origin main --force
```

Poi, come sempre: Portainer → Stacks → myband → **Pull and redeploy**.
