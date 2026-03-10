# 🚀 Guide d'Installation WAMP - Budgie

## 📋 Prérequis

- WAMP Server 3.x (avec PHP 8.2 et MySQL 8.0)
- Navigateur web moderne

---

## 🔧 Installation Pas à Pas

### **Étape 1 : Placement du Projet**

1. Copiez le dossier `PA` dans : `C:\wamp64\www\`
2. Le chemin final sera : `C:\wamp64\www\PA\`

### **Étape 2 : Configuration de la Base de Données**

1. **Démarrez WAMP** (icône verte dans la barre des tâches)

2. **Ouvrez phpMyAdmin** : 
   - Cliquez sur l'icône WAMP → phpMyAdmin
   - OU allez sur : `http://localhost/phpmyadmin`

3. **Créez la base de données** :
   ```sql
   CREATE DATABASE budgie_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Importez le schéma** :
   - Dans phpMyAdmin, sélectionnez la base `budgie_db`
   - Allez dans l'onglet "Import"
   - Choisissez le fichier : `C:\wamp64\www\PA\database\schema.sql`
   - Cliquez sur "Exécuter"

### **Étape 3 : Configuration des Variables d'Environnement**

**Option A : Sans fichier .env (Recommandé pour WAMP)**

Le projet utilise déjà les valeurs par défaut WAMP dans `config.php` :
- **DB_HOST** : `localhost`
- **DB_USER** : `root`
- **DB_PASS** : `` (vide par défaut)
- **DB_NAME** : `budgie_db`

**Option B : Avec fichier .env (Optionnel)**

Si vous voulez personnaliser :
1. Copiez `.env.wamp` en `.env`
2. Modifiez les valeurs si nécessaire

### **Étape 4 : Vérification Apache**

1. **Vérifiez que mod_rewrite est activé** :
   - Clic gauche sur icône WAMP
   - Apache → Apache modules
   - Assurez-vous que `rewrite_module` est coché ✓

2. **Redémarrez Apache** :
   - Clic sur icône WAMP → Restart All Services

### **Étape 5 : Accès à l'Application**

Ouvrez votre navigateur et allez sur :

```
http://localhost/PA
```

✅ **Vous serez automatiquement redirigé vers la page de connexion !**

---

## 🌐 URLs Disponibles

| Page | URL |
|------|-----|
| **Accueil** | `http://localhost/PA` |
| **Connexion** | `http://localhost/PA/public/login.php` |
| **Inscription** | `http://localhost/PA/public/signup.php` |
| **Dashboard** | `http://localhost/PA/public/index.php` |
| **Comptes** | `http://localhost/PA/public/accounts.php` |
| **Dépenses** | `http://localhost/PA/public/expenses.php` |
| **Revenus** | `http://localhost/PA/public/incomes.php` |
| **Prévisions** | `http://localhost/PA/public/forecasts.php` |
| **Profil** | `http://localhost/PA/public/profile.php` |
| **Admin** | `http://localhost/PA/public/admin.php` |

---

## 👤 Comptes de Test

Après import du schéma, vous avez 3 utilisateurs de test :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| `jean.dupont@example.com` | `password` | Utilisateur |
| `marie.martin@example.com` | `password` | Premium |
| `admin@budgie.com` | `password` | Admin |

---

## 🔧 Dépannage

### **Erreur 404 "Not Found"**

**Problème :** `http://web/login.php` ou URL incorrecte

**Solution :**
```
✗ http://web/login.php
✓ http://localhost/PA/public/login.php
✓ http://localhost/PA  (recommandé)
```

### **Erreur "Database connection failed"**

**Causes possibles :**

1. **Base de données non créée**
   - Vérifiez dans phpMyAdmin que `budgie_db` existe
   - Importez `database/schema.sql`

2. **Mauvais mot de passe MySQL**
   - Par défaut WAMP : user=`root`, password=`` (vide)
   - Si vous avez changé le mot de passe root, créez un fichier `.env`

3. **MySQL non démarré**
   - Icône WAMP doit être verte
   - Si orange/rouge : cliquez → Start All Services

### **Erreur "CSRF token invalid"**

**Solution :**
```php
// Videz le cache du navigateur
Ctrl + Shift + Delete
```

OU

```php
// Supprimez les sessions
C:\wamp64\tmp\
(supprimez les fichiers sess_*)
```

### **Page blanche sans erreur**

**Solution :**

1. Activez l'affichage des erreurs dans `config.php` :
```php
define('APP_ENV', 'development'); // ligne 17
```

2. Ou vérifiez les logs :
```
C:\wamp64\logs\php_error.log
C:\wamp64\logs\apache_error.log
```

### **Styles CSS ne chargent pas**

**Solution :**

Vérifiez le chemin dans le navigateur (F12 → Network) :
```
✓ http://localhost/PA/public/css/style.css
✗ http://localhost/css/style.css
```

Si incorrect, vérifiez `<base>` dans `header.php` ou `.htaccess`.

---

## 🎨 Structure pour WAMP

```
C:\wamp64\www\PA\
├── 📄 index.php              ← Point d'entrée (redirige)
├── 📄 .htaccess              ← Configuration Apache
├── 📁 public\                ← Pages accessibles
│   ├── login.php
│   ├── signup.php
│   ├── index.php (dashboard)
│   ├── css\
│   ├── js\
│   └── ...
├── 📁 src\                   ← Code backend
│   ├── config\
│   ├── services\
│   └── ...
├── 📁 database\
│   └── schema.sql            ← À importer
└── 📁 tests\
```

---

## 🎯 Configuration Recommandée WAMP

### **PHP 8.2+**
Vérifiez votre version :
```
http://localhost/?phpinfo=1
```

Ou dans WAMP : Clic gauche → PHP → Version → 8.2.x

### **MySQL 8.0+**
Icône WAMP → MySQL → Version → 8.0.x

### **Extensions PHP requises** (normalement actives)
- ✅ mysqli
- ✅ pdo_mysql
- ✅ mbstring
- ✅ openssl
- ✅ json

---

## ⚡ Raccourcis Rapides

### **Redémarrer WAMP**
```
Icône WAMP → Restart All Services
```

### **Logs PHP**
```
C:\wamp64\logs\php_error.log
```

### **phpMyAdmin rapide**
```
http://localhost/phpmyadmin
```

### **Tester la connexion DB**
Créez `test_db.php` dans `public/` :
```php
<?php
require_once __DIR__ . '/../src/config/config.php';
try {
    $db = getDB();
    echo "✅ Connexion DB réussie !";
    $result = fetchOne("SELECT COUNT(*) as count FROM users");
    echo "<br>👤 Utilisateurs : " . $result['count'];
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>
```

Puis allez sur : `http://localhost/PA/public/test_db.php`

---

## 🚀 Commandes Utiles

### **Vider le cache de session**
Supprimez :
```
C:\wamp64\tmp\sess_*
```

### **Réinitialiser la base de données**
Dans phpMyAdmin :
```sql
DROP DATABASE budgie_db;
CREATE DATABASE budgie_db;
```
Puis réimportez `schema.sql`

### **Changer le mot de passe root MySQL**
Dans phpMyAdmin :
```sql
ALTER USER 'root'@'localhost' IDENTIFIED BY 'nouveau_mdp';
FLUSH PRIVILEGES;
```

N'oubliez pas de mettre à jour `.env` ou `config.php`.

---

## 📞 Support

Si vous avez toujours des problèmes :

1. Vérifiez que WAMP est bien vert (pas orange/rouge)
2. Testez `http://localhost` (la page d'accueil WAMP doit s'afficher)
3. Vérifiez les logs d'erreur
4. Assurez-vous que le port 80 n'est pas utilisé par Skype/IIS

---

## ✅ Installation Réussie !

Si vous voyez la page de connexion Budgie, c'est bon ! 🎉

**Prochaines étapes :**
1. Créez un compte ou connectez-vous avec un compte test
2. Ajoutez des comptes
3. Créez des dépenses et revenus
4. Testez les prévisions

**Bonne utilisation de Budgie ! 🐦**
