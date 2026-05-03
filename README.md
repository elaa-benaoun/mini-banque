
# 🏦 Mini-Banque

Application web bancaire simulée permettant à un utilisateur de gérer son compte : inscription, connexion, dépôts, retraits, virements et historique des transactions.

---

## ✨ Fonctionnalités

- **Inscription / Connexion** sécurisée avec hachage `bcrypt` et sessions PHP
- **Tableau de bord** : affichage du solde en temps réel
- **Dépôt** d'argent sur le compte
- **Retrait** avec limite journalière configurable (défaut : 1 000 TND/jour) et barre de progression visuelle
- **Virement** vers un autre utilisateur par email
- **Historique des transactions** filtrable par type et par date
- **Page profil** avec informations personnelles

---

## 🛠️ Technologies

| Couche | Technologies |
|---|---|
| Frontend | PHP, HTML5, CSS3, JavaScript (Fetch API) |
| Backend (API REST) | PHP 8+ |
| Base de données | MySQL 8 — PDO avec requêtes préparées |
| Sécurité | `password_hash` / `password_verify`, sessions PHP, `htmlspecialchars` |

---

## 📁 Structure du projet

```
mini-banque/
├── config/
│   └── db.php              # Connexion PDO à la base de données
├── api/
│   ├── connexion.php       # POST /api/connexion — authentification
│   ├── inscription.php     # POST /api/inscription — création de compte
│   ├── solde.php           # GET  /api/solde — solde courant
│   ├── depot.php           # POST /api/depot — dépôt
│   ├── retrait.php         # POST /api/retrait — retrait (avec limite journalière)
│   ├── limite_retrait.php  # GET  /api/limite_retrait — état de la limite du jour
│   ├── virement.php        # POST /api/virement — virement entre comptes
│   ├── historique.php      # GET  /api/historique — liste des mouvements
│   └── profil.php          # GET  /api/profil — informations utilisateur
├── Frontend/
│   ├── login.php           # Page de connexion
│   ├── signup.php          # Page d'inscription
│   ├── index.php           # Tableau de bord principal
│   ├── profil.php          # Page profil utilisateur
│   ├── logout.php          # Déconnexion + destruction de session
│   ├── script.js           # Appels Fetch API côté client
│   └── style.css           # Styles globaux
└── init_database.sql       # Script d'initialisation de la BDD
```

---

## ⚙️ Installation & Lancement

### Prérequis
- **PHP 8+**
- **MySQL 8**
- **XAMPP** (ou WAMP / Laragon)

### Étapes

**1. Cloner le dépôt**
```bash
git clone https://github.com/elaa-benaoun/mini-banque.git
cd mini-banque
```

**2. Placer le projet dans le serveur local**
```
C:/xampp/htdocs/mini-banque/    ← XAMPP
```

**3. Initialiser la base de données**

Ouvrir **phpMyAdmin** → onglet SQL → coller et exécuter le contenu de `init_database.sql`.

Ou via terminal :
```bash
mysql -u root -p < init_database.sql
```

**4. Configurer la connexion BDD** (si besoin)

Éditer `config/db.php` :
```php
$host = '127.0.0.1';
$db   = 'mini_banque';
$user = 'root';
$pass = '';       // Votre mot de passe MySQL
```

**5. Lancer**

Démarrer Apache + MySQL dans XAMPP, puis ouvrir :
```
http://localhost/mini-banque/Frontend/login.php
```

---

## 🗄️ Schéma de la base de données

```sql
utilisateurs
  id, nom, prenom, email (UNIQUE), mot_de_passe (bcrypt),
  solde DECIMAL(12,2), date_creation, date_modification

mouvements
  id, utilisateur_id (FK), type ('depot'|'retrait'|'virement'),
  montant DECIMAL(12,2), solde_apres, description, date_mouvement
```

---

## 🔒 Sécurité

- Mots de passe hachés avec `password_hash()` (bcrypt)
- Requêtes SQL préparées via PDO (protection contre les injections SQL)
- Régénération de l'ID de session après connexion (`session_regenerate_id`)
- Sorties HTML échappées avec `htmlspecialchars()`
- Vérification d'authentification sur chaque endpoint API

---

## 🚀 Améliorations possibles

- Authentification par token JWT (API stateless)
- Ajout d'un vrai système de numéro de compte (RIB)
- Notifications par email pour chaque transaction
- Interface responsive mobile
- Déploiement avec Docker

---

## Auteur

**Elaa Ben Aoun** — Étudiante en Sciences Informatiques @ ENSI  
GitHub : [@elaa-benaoun](https://github.com/elaa-benaoun)  
LinkedIn : [elaa-ben-aoun](https://www.linkedin.com/in/elaa-ben-aoun-37b544281/)
