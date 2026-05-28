# Guide d'installation — ChronoSphere

## Prérequis

Installe ces outils avant de commencer :

| Outil | Version minimale | Lien |
|-------|-----------------|------|
| PHP | 8.3+ | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org |
| MySQL | 8.0+ | (inclus dans WAMP/XAMPP) |
| Node.js | 18+ | https://nodejs.org |
| Git | any | https://git-scm.com |

> **Recommandé sur Windows** : utilise [WAMP](https://www.wampserver.com) — il fournit Apache + PHP + MySQL en un seul installeur.

Extensions PHP requises (activées par défaut dans WAMP) :
`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`

---

## Installation pas à pas

### 1. Cloner le projet

```bash
git clone https://github.com/mohamedbalde0553-glitch/Chronosphere.git
cd Chronosphere
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Ouvre `.env` et modifie la section base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chronosphere
DB_USERNAME=root
DB_PASSWORD=
```

> Si tu utilises WAMP, le mot de passe root est vide par défaut.

### 4. Créer la base de données

Dans phpMyAdmin (ou MySQL CLI) :

```sql
CREATE DATABASE chronosphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Lancer les migrations et les seeders

```bash
php artisan migrate --seed
```

Cette commande crée toutes les tables et insère les données de démonstration (employés, shifts, congés, emplois du temps...).

> Durée estimée : 2 à 5 minutes selon la machine.

### 6. Installer les assets frontend

```bash
npm install
npm run build
```

### 7. Lancer l'application

**Option A — avec WAMP :**
Place le dossier `Chronosphere/` dans `C:\wamp64\www\` et accède à :
```
http://localhost/Chronosphere/public
```

**Option B — avec le serveur intégré Laravel :**
```bash
php artisan serve
```
Puis ouvre : `http://localhost:8000`

---

## Comptes de test

Voir le fichier [COMPTES_TEST.md](COMPTES_TEST.md).

Mot de passe universel : **`password`**

| Rôle | Email |
|------|-------|
| `hr_manager` | `baye.ueye91@microfinance.mf` |
| `hr_employee` | `madou.iallo1@microfinance.mf` |

---

## En cas de problème

| Erreur | Solution |
|--------|----------|
| `SQLSTATE: could not find driver` | Activer l'extension `pdo_mysql` dans `php.ini` |
| Page blanche / erreur 500 | Vérifier `storage/logs/laravel.log` |
| Assets CSS/JS manquants | Relancer `npm run build` |
| `php artisan` introuvable | Vérifier que PHP est dans le PATH système |
