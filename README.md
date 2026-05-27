# ChronoSphere

Application web de **gestion du temps** couvrant 5 domaines : emplois du temps universitaires, gestion RH des employés, agenda personnel, réservation de ressources et suivi de projets.

Développée avec **Laravel 11**, **Tailwind CSS v4**, **Alpine.js** et **FullCalendar v6**.

---

## Prérequis

| Outil | Version minimale |
|-------|-----------------|
| PHP | 8.2+ (8.3 recommandé) |
| Composer | 2.x |
| Node.js | 18+ (24 recommandé) |
| npm | 9+ |
| MySQL | 8.0+ (9.1 testé) |
| Serveur web | Apache 2.4 / WAMP64 / Nginx |

Extensions PHP requises : `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `bcmath`, `fileinfo`, `gd`.

---

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-depot> chronosphere
cd chronosphere
```

### 2. Dépendances PHP

```bash
composer install
```

### 3. Fichier d'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Modifier `.env` :

```env
APP_NAME=ChronoSphere
APP_URL=http://localhost/chronosphere/public   # adapter selon votre config

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chronosphere
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Base de données

Créer la base :

```sql
CREATE DATABASE chronosphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Exécuter les migrations (57 tables) :

```bash
php artisan migrate
```

Peupler avec les données initiales (rôles, permissions, utilisateurs démo, données d'exemple) :

```bash
php artisan db:seed
```

### 5. Dépendances frontend

```bash
npm install
npm run build
```

> En développement, utiliser `npm run dev` à la place de `npm run build` pour le rechargement à chaud.
> Après avoir arrêté `npm run dev`, supprimer `public/hot` si le fichier persiste.

---

## Démarrage

Avec WAMP/XAMPP, placer le projet dans `www/` et accéder à :

```
http://localhost/chronosphere/public
```

Avec le serveur intégré PHP :

```bash
php artisan serve
# → http://localhost:8000
```

---

## Comptes de démonstration

| Rôle | Email | Mot de passe | Accès |
|------|-------|--------------|-------|
| Super Admin | `admin@chronosphere.local` | `password` | Tous les modules |
| Utilisateur Démo | `demo@chronosphere.local` | `password` | Agenda, Timetable (lecture), Projet |

---

## Modules

### 1. Universitaire (Timetable)
Gestion complète des emplois du temps.
- **Entités** : Facultés, Niveaux, Années académiques, Salles, Matières, Groupes, Enseignants
- **Planning** : Grille hebdomadaire FullCalendar avec drag-and-drop
- **Conflits** : Détection automatique (salle / enseignant / groupe)
- **Export** : PDF (jsPDF), Excel (Maatwebsite)
- Accès : `/timetable`

### 2. Employés — RH (Shifts)
Gestion des ressources humaines et de la planification.
- **Trombinoscope** : cards employés avec filtres et fiche détaillée (4 onglets)
- **Planning** : FullCalendar timeGrid avec gestion des shifts
- **Horaires périodiques** : modèles hebdomadaires avec overrides par employé
- **Congés** : workflow Demande → Approbation/Rejet avec annulation automatique des shifts
- **Rapports** : KPIs + export PDF/Excel par département et période
- **API REST** : endpoints complets + authentification Sanctum
- Accès : `/shifts`

### 3. Agenda (Calendar)
Calendriers personnels et partagés.
- Multi-calendriers avec couleurs personnalisées
- Événements récurrents
- Vues : semaine, mois, liste
- Accès : `/calendar`

### 4. Réservation (Booking)
Réservation de salles et ressources partagées.
- **Ressources** : catégorisées, avec capacité et lieu
- **Calendrier** : vue par ressource ou globale
- **Workflow** : `pending → confirmed / rejected / cancelled`
- **Approbation** : manuelle ou automatique selon la ressource
- Accès : `/booking`

### 5. Projet / Gantt
Suivi de projets avec Kanban et diagramme de Gantt.
- **Kanban** : colonnes personnalisées, drag-and-drop SortableJS
- **Gantt** : frappe-gantt avec redimensionnement des barres
- **Tâches** : assignation, priorité, progression (0–100 %), commentaires
- Accès : `/project`

---

## API REST

L'API REST (Module 2 — Employés) est documentée dans [`docs/API_DOCUMENTATION.md`](docs/API_DOCUMENTATION.md).

**Base URL** : `/api`

**Authentification** :
```http
POST /api/auth/login
Content-Type: application/json
{ "email": "admin@chronosphere.local", "password": "password" }
```

Réponse : `{ "token": "...", "user": {...} }`

Utilisation du token :
```http
Authorization: Bearer <token>
```

**Endpoints principaux** :

| Méthode | URI | Description |
|---------|-----|-------------|
| `POST` | `/api/auth/login` | Connexion, retourne un token Bearer |
| `POST` | `/api/auth/logout` | Révocation du token |
| `GET` | `/api/employees` | Liste paginée des employés |
| `POST` | `/api/employees` | Créer un employé |
| `GET` | `/api/employees/{id}` | Détail d'un employé |
| `PUT` | `/api/employees/{id}` | Modifier un employé |
| `DELETE` | `/api/employees/{id}` | Supprimer (soft delete) |
| `GET` | `/api/employees/{id}/shifts` | Shifts d'un employé |
| `GET` | `/api/employees/{id}/leave-requests` | Congés d'un employé |
| `GET` | `/api/work-schedules` | Liste des horaires périodiques |
| `POST` | `/api/work-schedules/{id}/generate-shifts` | Générer des shifts depuis un horaire |
| `GET` | `/api/employees/{id}/schedule` | Horaire actif d'un employé |

---

## Tests

```bash
php artisan test
```

**158 tests Feature** couvrant :
- Auth API (login, logout, token invalide)
- CRUD Employés + sous-ressources (shifts, congés)
- Contrôle d'accès par rôle (super_admin / hr_manager / hr_employee)
- Horaires périodiques (CRUD, génération de shifts, overrides)
- Timetable, Booking, Projet, Shifts web

---

## Commandes utiles

```bash
# Vider tous les caches Laravel
php artisan optimize:clear

# Rebuild frontend en production
npm run build

# Dev server avec hot reload
npm run dev

# Lancer migrations + seeders (reset complet)
php artisan migrate:fresh --seed

# Liste des routes
php artisan route:list

# Lancer les tests
php artisan test
```

---

## Structure du projet

```
app/
├── Http/Controllers/Api/      # Contrôleurs API REST (Sanctum)
├── Modules/
│   ├── Calendar/              # Module Agenda
│   ├── Timetable/             # Module Universitaire
│   ├── Shifts/                # Module RH / Employés
│   │   └── Services/          # WorkScheduleService, ShiftService…
│   ├── Booking/               # Module Réservation
│   └── Project/               # Module Projet / Gantt
├── Policies/                  # EmployeePolicy (contrôle accès API)
└── Providers/                 # AppServiceProvider + ModuleServiceProviders

resources/
├── css/app.css                # Tailwind v4 + utilitaires globaux
├── js/
│   ├── app.js                 # Alpine.js init
│   └── {module}.js            # JS par module (FullCalendar, SortableJS…)
└── views/
    ├── layouts/               # app.blade.php, guest.blade.php
    ├── dashboard.blade.php
    └── modules/{module}/      # Vues par module

routes/
├── web.php                    # Routes auth + dashboard
├── api.php                    # Routes API REST
└── modules/                   # Routes web par module

tests/Feature/                 # 158 tests PHPUnit
docs/                          # Documentation technique
```

---

## Licence

Projet académique — usage interne.
