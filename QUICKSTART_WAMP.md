# 🚀 DÉMARRAGE RAPIDE - WAMP

## ⚡ Installation en 5 Minutes

### 1️⃣ **Copiez le projet**
```
C:\wamp64\www\PA\
```

### 2️⃣ **Créez la base de données**
Ouvrez phpMyAdmin : http://localhost/phpmyadmin

```sql
CREATE DATABASE budgie_db;
```

### 3️⃣ **Importez le schéma**
- Sélectionnez `budgie_db`
- Onglet "Import"
- Choisissez `database/schema.sql`
- Cliquez "Exécuter"

### 4️⃣ **Testez l'installation**
```
http://localhost/PA/test.php
```

✅ Si tout est vert, allez sur :
```
http://localhost/PA
```

---

## 🔑 Compte de Test

Email : `admin@budgie.com`  
Mot de passe : `password`

---

## ❌ Problèmes ?

### "Database connection failed"
```bash
1. Vérifiez que WAMP est vert (pas orange)
2. Créez la base budgie_db dans phpMyAdmin
3. Importez database/schema.sql
```

### "404 Not Found"
```bash
✗ NE PAS utiliser : http://web/login.php
✓ Utilisez : http://localhost/PA
```

### "CSRF token invalid"
```bash
Videz le cache : Ctrl + Shift + Delete
```

---

## 📖 Guide Complet

Consultez `WAMP_SETUP.md` pour plus de détails.

---

**Bon développement ! 🐦**
