# 🎓 Guide Étudiant - Budgie

Ce document explique les améliorations "real-world" apportées à Budgie pour la soutenance.

---

## 🔐 **1. Système d'Authentification Amélioré**

### **A. Indicateur de Force du Mot de Passe**
**Fichiers :** `public/signup.php`, `public/reset_password.php`

**Fonctionnement :**
```javascript
// Calcul de la force basé sur :
- Longueur (min 8 caractères) : +25 points
- Longueur >= 12 caractères : +25 points
- Majuscules ET minuscules : +25 points
- Chiffres présents : +15 points
- Symboles spéciaux : +10 points

// Résultat visuel :
- < 40 points = Rouge (Faible)
- 40-70 points = Orange (Moyen)
- > 70 points = Vert (Fort)
```

**Pourquoi c'est important :**
- Encourage les utilisateurs à créer des mots de passe sécurisés
- Feedback visuel en temps réel
- Standard des applications modernes (Gmail, Facebook, etc.)

---

### **B. Validation Email Intelligente**
**Fichier :** `public/signup.php`

**Fonctionnalités :**
1. **Détection des fautes de frappe courantes**
   ```javascript
   gmial.com → Suggère gmail.com
   hotmial.com → Suggère hotmail.com
   ```

2. **Blocage des emails temporaires**
   ```javascript
   // Refusé :
   - tempmail.com
   - guerrillamail.com
   - 10minutemail.com
   ```

**Pourquoi c'est important :**
- Réduit les erreurs d'inscription
- Empêche les comptes spam
- Améliore la qualité de la base utilisateurs

---

### **C. Système "Remember Me"**
**Fichiers :** `public/login.php`, `public/auth/login_process.php`

**Fonctionnement :**
```php
// Sans "Remember Me" : session de 7 jours
// Avec "Remember Me" : session de 30 jours

if ($rememberMe) {
    setcookie('remember_token', $sessionToken, 30 jours);
}
```

**Pourquoi c'est important :**
- Améliore l'expérience utilisateur
- Standard des sites web professionnels
- Sécurisé avec httpOnly cookie

---

## 🔑 **2. Réinitialisation du Mot de Passe**

### **Flow Complet**
**Fichiers créés :**
- `public/forgot_password.php`
- `public/auth/forgot_password_process.php`
- `public/reset_password.php`
- `public/auth/reset_password_process.php`

**Étapes :**
1. Utilisateur entre son email
2. Token généré et stocké en DB (`password_reset_tokens`)
3. Lien envoyé (en dev, affiché sur la page login)
4. Lien valide 1 heure
5. Token utilisé une seule fois (`used_at`)

**Mesures de sécurité :**
```php
// Rate limiting : 3 tentatives par heure
checkRateLimit("password_reset_{$email}", 3, 3600)

// Ne révèle pas si l'email existe
// Message identique pour email existant ou non

// Token avec expiration
expires_at > NOW()
```

**Pourquoi c'est important :**
- Fonctionnalité essentielle de toute application
- Montre la maîtrise des flows complexes
- Sécurité renforcée (pas d'énumération d'emails)

---

## 📄 **3. Pages Légales**

### **A. Conditions d'Utilisation**
**Fichier :** `public/terms.php`

**Contenu :**
- Acceptation des conditions
- Description du service
- Responsabilités utilisateur
- Plans d'abonnement
- Utilisation acceptable
- Propriété intellectuelle
- Limitation de responsabilité
- Résiliation
- Contact

### **B. Politique de Confidentialité (RGPD)**
**Fichier :** `public/privacy.php`

**Contenu :**
- Données collectées
- Utilisation des données
- Protection (encryption, CSRF, rate limiting)
- Partage des données
- Droits RGPD (accès, rectification, suppression, export)
- Cookies
- Conservation des données
- Contact DPO

**Pourquoi c'est important :**
- **Obligatoire légalement** en Europe (RGPD)
- Montre la professionnalisation du projet
- Case à cocher lors de l'inscription
- Rassure les utilisateurs sur la sécurité

---

## 👤 **4. Profil Amélioré**

### **Nouvelles Fonctionnalités**
**Fichier :** `public/profile.php`

**Ajouts :**
1. **Données réelles** (pas simulées)
   ```php
   $user = getCurrentUser();
   $accountsCount = fetchOne("SELECT COUNT(*) ...");
   ```

2. **Dernière connexion affichée**
   ```php
   formatDate($user_data['last_login'], 'd/m/Y H:i')
   ```

3. **Adresse IP actuelle**
   ```php
   $_SERVER['REMOTE_ADDR']
   ```

4. **Sessions actives** (bouton pour future implémentation)

**Pourquoi c'est important :**
- Transparence pour l'utilisateur
- Détection d'activités suspectes
- Standard des apps modernes (Google, Facebook)

---

## 🔒 **5. Validation Renforcée**

### **Côté Client (JavaScript)**
**Fichiers :** `signup.php`, `reset_password.php`

**Validations en temps réel :**
```javascript
// Vérification correspondance mots de passe
✓ Les mots de passe correspondent
✗ Les mots de passe ne correspondent pas

// Force du mot de passe (barre de progression)
// Détection fautes frappe email
// Blocage emails temporaires
```

### **Côté Serveur (PHP)**
**Fichier :** `public/auth/signup_process.php`

**Validations :**
```php
// Exigences minimales
- 8 caractères minimum
- Au moins 1 minuscule
- Au moins 1 chiffre

// Vérification case "Accepter les CGU"
if (!isset($_POST['terms'])) {
    $errors[] = 'Vous devez accepter les conditions';
}
```

**Pourquoi c'est important :**
- **Double validation** (client + serveur)
- Sécurité renforcée
- UX améliorée (erreurs en temps réel)

---

## 📊 **6. Logs et Traçabilité**

### **Nouveaux événements loggés**
**Fichiers :** `forgot_password_process.php`, `reset_password_process.php`

**Événements :**
```php
'auth.password_reset_requested'  // Demande reset
'auth.password_reset_unknown_email'  // Email inconnu
'auth.password_reset_completed'  // Reset réussi
```

**Pourquoi c'est important :**
- Audit trail complet
- Détection fraude
- Debug facilité
- Conformité RGPD (traçabilité)

---

## 🎯 **Points à Mentionner en Soutenance**

### **Sécurité**
✅ Hachage bcrypt des mots de passe  
✅ Protection CSRF sur tous les formulaires  
✅ Rate limiting (login, signup, reset)  
✅ Sessions sécurisées avec tokens  
✅ Requêtes préparées (SQL injection)  
✅ HttpOnly cookies  
✅ Validation double (client + serveur)  
✅ Pas d'énumération d'emails (reset password)  

### **UX / UI**
✅ Feedback visuel en temps réel  
✅ Suggestions intelligentes (email)  
✅ Messages d'erreur clairs  
✅ Flow de reset password complet  
✅ Remember me (30 jours)  
✅ Dernière connexion affichée  

### **Légal / RGPD**
✅ Conditions d'utilisation  
✅ Politique de confidentialité  
✅ Case à cocher CGU obligatoire  
✅ Droits utilisateurs expliqués  
✅ Traçabilité complète (logs)  

### **Code Quality**
✅ Code commenté et lisible  
✅ Séparation des responsabilités  
✅ Validation côté serveur systématique  
✅ Gestion d'erreurs complète  
✅ Mode développement pour debug  

---

## 🚀 **Démonstration Suggérée**

### **1. Inscription** (3 min)
- Montrer l'indicateur de force mot de passe
- Tester la suggestion email (gmial.com)
- Montrer la case CGU obligatoire
- Créer un compte

### **2. Mot de passe oublié** (2 min)
- Demander un reset
- Montrer le lien en mode dev
- Réinitialiser le mot de passe
- Se connecter avec le nouveau

### **3. Profil** (2 min)
- Afficher dernière connexion
- Montrer statistiques réelles
- Parcourir les sections sécurité

### **4. Pages légales** (1 min)
- Ouvrir CGU
- Ouvrir Politique confidentialité
- Montrer le contenu RGPD

---

## 💡 **Questions Fréquentes**

**Q: Pourquoi pas d'email réel ?**
R: Nécessite configuration SMTP. En production, on utiliserait SendGrid/Mailgun. Le flow est complet, seul l'envoi réel manque.

**Q: Les tokens sont-ils sécurisés ?**
R: Oui, générés avec `random_bytes(32)` puis `bin2hex()` = 64 caractères aléatoires cryptographiquement sûrs.

**Q: Pourquoi 1 heure pour reset password ?**
R: Standard industrie. Équilibre entre sécurité et UX.

**Q: RGPD obligatoire ?**
R: Oui en Europe pour tout site collectant des données personnelles (email, nom). Amende jusqu'à 4% du CA mondial.

---

## 📚 **Ressources**

- [OWASP Password Storage](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)
- [RGPD Officiel](https://www.cnil.fr/fr/rgpd-de-quoi-parle-t-on)
- [PHP Password Hashing](https://www.php.net/manual/fr/function.password-hash.php)
- [CSRF Protection](https://owasp.org/www-community/attacks/csrf)

---

**Créé pour la soutenance Budgie - <?php echo date('Y'); ?>**
