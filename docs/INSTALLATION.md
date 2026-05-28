# Guide d'installation — ChronoSphere

## Prérequis

Installe ces outils **avant** de commencer. Vérifie les versions avec les commandes indiquées.

| Outil | Version minimale | Vérification | Lien |
|-------|-----------------|--------------|------|
| PHP | **8.3+** | `php -v` | https://www.php.net/downloads |
| Composer | 2.x | `composer -V` | https://getcomposer.org |
| MySQL | 8.0+ | inclus dans WAMP | — |
| Node.js | 18+ | `node -v` | https://nodejs.org |
| npm | 9+ | `npm -v` | (inclus avec Node.js) |
| Git | any | `git --version` | https://git-scm.com |

> **Recommandé sur Windows** : installe [WAMP 3.3+](https://www.wampserver.com) — il fournit Apache + PHP 8.3 + MySQL en un seul installeur. Assure-toi de démarrer WAMP (icône verte dans la barre des tâches) avant de commencer.

**Extensions PHP requises** (activées par défaut dans WAMP) :
`pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `zip`

Pour vérifier qu'une extension est active : `php -m | findstr pdo_mysql` (Windows) ou `php -m | grep pdo_mysql` (Linux/Mac).

---

## Installation pas à pas

> Toutes les commandes s'exécutent dans un **terminal ouvert à la racine du projet**.
> Sur Windows, utilise **Git Bash**, **PowerShell**, ou l'invite de commande.

---

### Étape 1 — Cloner le projet

```bash
git clone https://github.com/mohamedbalde0553-glitch/Chronosphere.git
cd Chronosphere
```

---

### Étape 2 — Installer les dépendances PHP

```bash
composer install
```

> Cette commande télécharge toutes les bibliothèques Laravel. Durée : 1 à 3 minutes selon la connexion.

---

### Étape 3 — Créer le fichier de configuration

**Sur Windows (PowerShell ou CMD) :**
```powershell
copy .env.example .env
```

**Sur Linux / Mac / Git Bash :**
```bash
cp .env.example .env
```

Ensuite, génère la clé de l'application :
```bash
php artisan key:generate
```

---

### Étape 4 — Configurer la base de données

Ouvre le fichier `.env` (avec VS Code, Notepad++, ou n'importe quel éditeur) et remplace la section base de données par :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chronosphere
DB_USERNAME=root
DB_PASSWORD=
```

> Avec WAMP, le mot de passe root est **vide** par défaut.
> Si tu as défini un mot de passe lors de l'installation de MySQL, indique-le à la ligne `DB_PASSWORD=`.

Adapte aussi l'URL de l'application selon ton mode de lancement (voir Étape 7) :
```env
# Si tu utilises php artisan serve :
APP_URL=http://localhost:8000

# Si tu utilises WAMP directement :
APP_URL=http://localhost/Chronosphere/public
```

---

### Étape 5 — Créer la base de données

Dans **phpMyAdmin** (`http://localhost/phpmyadmin`) → onglet SQL → exécute :

```sql
CREATE DATABASE chronosphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ou depuis le terminal MySQL :
```bash
mysql -u root -e "CREATE DATABASE chronosphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

### Étape 6 — Lancer les migrations et les seeders

```bash
php artisan migrate --seed
```

> Cette commande crée toutes les tables et insère les données de démonstration :
> 157 employés répartis dans 8 départements, shifts, congés, cours universitaires, salles, groupes...
>
> **Durée estimée : 3 à 7 minutes.** C'est normal si ça prend du temps.

---

### Étape 7 — Créer le lien de stockage

```bash
php artisan storage:link
```

> Nécessaire pour que les fichiers uploadés soient accessibles depuis le navigateur.

---

### Étape 8 — Installer et compiler les assets frontend

```bash
npm install
npm run build
```

> Si tu veux modifier les styles/scripts en temps réel (mode développement) :
> ```bash
> npm run dev
> ```
> Laisse ce terminal ouvert pendant que tu travailles.

---

### Étape 9 — Lancer l'application

**Option A — Serveur intégré Laravel (plus simple) :**
```bash
php artisan serve
```
Ouvre ensuite : **http://localhost:8000**

**Option B — avec WAMP :**
Place le dossier `Chronosphere/` dans `C:\wamp64\www\` puis accède à :
**http://localhost/Chronosphere/public**

> Si tu as une erreur 403 avec WAMP, vérifie que le module `mod_rewrite` est activé (clic droit sur l'icône WAMP → Apache → Modules Apache → mod_rewrite).

---

## Vérification — est-ce que tout fonctionne ?

1. Ouvre l'URL de l'application dans ton navigateur
2. Tu dois voir la page de connexion ChronoSphere
3. Connecte-toi avec un des comptes de test ci-dessous
4. Tu dois arriver sur le dashboard du Module 2 (Gestion des Shifts)

Si l'une de ces étapes échoue, consulte la section **En cas de problème** plus bas.

---

## Comptes de test — Module 2

Mot de passe universel : **`password`**

| Rôle | Email | Ce que tu verras |
|------|-------|-----------------|
| `hr_manager` | `baye.ueye91@microfinance.mf` | Dashboard complet : tous les employés, shifts, congés, statistiques |
| `hr_employee` | `madou.iallo1@microfinance.mf` | Dashboard personnel : uniquement ses propres shifts et demandes de congé |

---

## En cas de problème

| Symptôme | Cause probable | Solution |
|----------|---------------|----------|
| `could not find driver` | Extension `pdo_mysql` inactive | Dans WAMP : clic sur l'icône → PHP → Extensions PHP → cocher `pdo_mysql` |
| Page blanche ou erreur 500 | Erreur dans le code ou la config | Ouvre `storage/logs/laravel.log` et lis la dernière erreur |
| `APP_KEY` vide | Étape 3 non faite | Relance `php artisan key:generate` |
| CSS/JS absents (page non stylisée) | Build frontend non fait | Relance `npm run build` |
| `Class not found` après `git pull` | Nouveau fichier PHP non chargé | Relance `composer install` |
| `Table not found` | Migration non exécutée | Relance `php artisan migrate --seed` |
| `php artisan` introuvable | PHP absent du PATH | Ajoute le dossier PHP de WAMP au PATH système, ou utilise le chemin complet `C:\wamp64\bin\php\php8.3.x\php.exe` |
| Erreur 403 sur WAMP | mod_rewrite désactivé | Activer mod_rewrite dans WAMP (voir Étape 9) |

**Commandes utiles si quelque chose ne s'affiche pas correctement :**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
