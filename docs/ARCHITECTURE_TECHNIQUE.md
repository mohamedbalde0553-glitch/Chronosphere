# Architecture Technique — ChronoSphere

## Table des matières

1. [Stack technique](#1-stack-technique)
2. [Structure des répertoires](#2-structure-des-répertoires)
3. [Architecture modulaire](#3-architecture-modulaire)
4. [Schéma base de données](#4-schéma-base-de-données)
5. [API REST (Sanctum)](#5-api-rest-sanctum)
6. [Couche Services, Requests et Policies](#6-couche-services-requests-et-policies)
7. [Authentification et contrôle d'accès](#7-authentification-et-contrôle-daccès)
8. [Frontend : build Vite et conventions CSS](#8-frontend--build-vite-et-conventions-css)
9. [Stratégie de tests](#9-stratégie-de-tests)

---

## 1. Stack technique

### Backend

| Composant | Version | Rôle |
|-----------|---------|------|
| PHP | 8.3 | Runtime |
| Laravel | 11 | Framework MVC |
| MySQL | 9.1 | Base de données relationnelle |
| Spatie laravel-permission | 6.x | Rôles et permissions (RBAC) |
| Laravel Sanctum | 4.x | Authentification API par token Bearer |
| Maatwebsite Excel | 3.x | Export Excel (XLSX) |
| dompdf / barryvdh | 3.x | Export PDF (A4 paysage) |
| Laravel Telescope | 5.x | Débogage et introspection (dev) |

### Frontend

| Composant | Version | Rôle |
|-----------|---------|------|
| Vite | 6.x | Build system, HMR |
| Tailwind CSS | 4.x | Utilitaires CSS (syntaxe `@import 'tailwindcss'`) |
| Alpine.js | 3.x | Réactivité légère côté client |
| FullCalendar | 6.x | Calendriers interactifs (timeGrid, dayGrid, list) |
| SortableJS | 1.x | Drag-and-drop Kanban |
| frappe-gantt | 0.x | Diagramme de Gantt |
| Chart.js | 4.x | Graphiques (barres, statistiques RH) |

---

## 2. Structure des répertoires

```
chronosphere/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # Contrôleurs API REST (Sanctum)
│   │   │   └── Auth/         # Authentification web (Breeze)
│   │   ├── Middleware/        # EnsureModuleAccess, SetUserTimezone…
│   │   ├── Requests/          # Form Requests globaux (Login, Profile)
│   │   └── Resources/Api/    # JSON Resources (EmployeeResource…)
│   ├── Models/                # Modèles partagés (User, Profile, Tag…)
│   ├── Modules/               # Un sous-dossier par domaine métier
│   │   ├── Timetable/
│   │   ├── Shifts/
│   │   ├── Calendar/
│   │   ├── Booking/
│   │   └── Project/
│   ├── Policies/              # EmployeePolicy (RBAC API)
│   └── Providers/             # AppServiceProvider, TelescopeServiceProvider
│
├── bootstrap/
│   └── providers.php          # Enregistrement des ServiceProviders
│
├── database/
│   ├── migrations/            # 57 migrations ordonnées par préfixe
│   └── seeders/               # RoleSeeder, AdminUserSeeder…
│
├── resources/
│   ├── css/app.css            # Tailwind v4 + utilitaires globaux (.card-hover…)
│   ├── js/
│   │   ├── app.js             # Bootstrap Alpine.js
│   │   └── {module}.js        # JS par module (FullCalendar, SortableJS…)
│   └── views/
│       ├── layouts/           # app.blade.php (auth), guest.blade.php
│       ├── dashboard.blade.php
│       └── modules/{module}/  # Vues Blade par module
│
├── routes/
│   ├── web.php                # Auth + dashboard
│   ├── api.php                # API REST (Sanctum)
│   └── modules/               # Routes web par module
│
├── tests/
│   └── Feature/               # Tests PHPUnit (158 tests)
│
└── docs/                      # Documentation technique et utilisateur
```

---

## 3. Architecture modulaire

Chaque domaine métier est encapsulé dans `app/Modules/{NomModule}/` et suit la structure suivante :

```
Modules/{NomModule}/
├── Http/
│   ├── Controllers/   # Contrôleurs web Blade
│   └── Requests/      # Form Requests spécifiques au module
├── Models/            # Éloquent models du module
├── Services/          # Logique métier (module Shifts uniquement)
└── Providers/         # {NomModule}ServiceProvider.php
```

### ServiceProviders

Chaque module déclare un `ServiceProvider` enregistré dans `bootstrap/providers.php` :

```php
// bootstrap/providers.php
return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Timetable\Providers\TimetableServiceProvider::class,
    App\Modules\Shifts\Providers\ShiftsServiceProvider::class,
    App\Modules\Calendar\Providers\CalendarServiceProvider::class,
    App\Modules\Booking\Providers\BookingServiceProvider::class,
    App\Modules\Project\Providers\ProjectServiceProvider::class,
];
```

Chaque provider charge les routes du module depuis `routes/modules/{module}.php`.

### Middleware de contrôle d'accès

`EnsureModuleAccess` vérifie que l'utilisateur authentifié possède le rôle requis avant d'accéder à un module. Les routes web des modules sont toutes protégées par `auth` + `EnsureModuleAccess`.

---

## 4. Schéma base de données

Les tables sont préfixées par domaine, facilitant la lisibilité et les migrations ciblées.

### Tables globales (sans préfixe)

| Table | Description |
|-------|-------------|
| `users` | Comptes utilisateurs (email, password, timezone, locale) |
| `profiles` | Informations étendues (avatar, bio, téléphone) |
| `settings` | Paires clé/valeur par utilisateur |
| `tags` | Tags globaux (polymorph) |
| `favorites` | Favoris polymorphes |
| `attachments` | Fichiers joints polymorphes |
| `notifications` | Notifications Laravel (JSON payload) |
| `personal_access_tokens` | Tokens Sanctum |
| `roles`, `permissions`, `model_has_roles`… | Tables Spatie permission (4 tables) |
| `jobs`, `cache` | Files d'attente et cache |

### Module Universitaire — préfixe `uni_`

| Table | Description |
|-------|-------------|
| `uni_academic_years` | Années académiques |
| `uni_faculties` | Facultés |
| `uni_levels` | Niveaux (Licence, Master…) |
| `uni_semesters` | Semestres |
| `uni_class_groups` | Groupes d'étudiants (parent/enfant) |
| `uni_subjects` | Matières (code, ECTS, couleur) |
| `uni_rooms` | Salles (type, capacité, bâtiment) |
| `uni_time_slots` | Créneaux horaires de référence |
| `uni_teachers` | Enseignants liés à un `users.id` |
| `uni_students` | Étudiants |
| `uni_subject_teacher` | Pivot matière ↔ enseignant |
| `uni_courses` | Cours (matière + groupe) |
| `uni_course_sessions` | Séances planifiées (salle, enseignant, horaire) |

### Module RH / Employés — préfixe `hr_`

| Table | Description |
|-------|-------------|
| `hr_departments` | Départements |
| `hr_positions` | Postes |
| `hr_employees` | Employés (lien `users.id`, statut, salaire en centimes) |
| `hr_skills` | Compétences (pivot `employee_skill` avec niveau 1–5) |
| `hr_shift_types` | Types de shifts (couleur, durée standard) |
| `hr_shifts` | Shifts individuels (durée en **minutes**) |
| `hr_leave_requests` | Demandes de congé (workflow pending → approved/rejected) |
| `hr_work_schedules` | Horaires périodiques (modèles hebdomadaires) |
| `hr_work_schedule_days` | Jours configurés par horaire (heure début/fin, pause en minutes) |
| `hr_employee_schedule_overrides` | Overrides par employé sur une période |

> **Règle critique** : toutes les durées sont stockées en **minutes** (jamais en décimal).

### Module Agenda — préfixe `cal_`

| Table | Description |
|-------|-------------|
| `cal_event_categories` | Catégories d'événements |
| `cal_calendars` | Calendriers par utilisateur (couleur) |
| `cal_events` | Événements (récurrence iCal, tout-jour ou horaire) |
| `cal_event_reminders` | Rappels par événement |
| `cal_event_invitations` | Invitations (statut accepted/declined/pending) |
| `cal_event_shares` | Partage de calendriers entre utilisateurs |

### Module Réservation — préfixe `booking_`

| Table | Description |
|-------|-------------|
| `booking_resource_categories` | Catégories de ressources |
| `booking_resources` | Ressources réservables (capacité, approbation auto/manuelle) |
| `booking_resource_availabilities` | Plages de disponibilité des ressources |
| `booking_bookings` | Réservations (statut: pending/confirmed/rejected/cancelled) |
| `booking_waitlist` | Liste d'attente |

### Module Projet / Gantt — préfixe `project_`

| Table | Description |
|-------|-------------|
| `project_projects` | Projets (statut, couleur, budget, progression calculée) |
| `project_teams` | Équipes (pivot projet ↔ membres) |
| `project_tasks` | Tâches (colonne Kanban, priorité, progression 0–100 %) |
| `project_task_dependencies` | Dépendances entre tâches |
| `project_task_assignments` | Assignations de tâches à des utilisateurs |
| `project_task_comments` | Commentaires de tâche |

---

## 5. API REST (Sanctum)

### Authentification

L'API utilise **Laravel Sanctum** avec des tokens Bearer (table `personal_access_tokens`).

```
POST /api/auth/login
Content-Type: application/json
{ "email": "...", "password": "..." }

→ { "token": "...", "user": { "id": ..., "name": "...", "roles": [...] } }
```

Toutes les routes protégées requièrent :
```
Authorization: Bearer <token>
```

### Endpoints

| Méthode | URI | Rôle requis | Description |
|---------|-----|-------------|-------------|
| `POST` | `/api/auth/login` | — | Connexion, retourne un token |
| `POST` | `/api/auth/logout` | any | Révocation du token courant |
| `GET` | `/api/departments` | any | Liste des départements |
| `GET` | `/api/positions` | any | Liste des postes |
| `GET` | `/api/employees` | any | Liste paginée des employés |
| `POST` | `/api/employees` | hr_manager+ | Créer un employé |
| `GET` | `/api/employees/{id}` | any | Détail d'un employé |
| `PUT` | `/api/employees/{id}` | hr_manager+ | Modifier un employé |
| `DELETE` | `/api/employees/{id}` | super_admin | Soft delete |
| `GET` | `/api/employees/{id}/shifts` | any | Shifts d'un employé |
| `GET` | `/api/employees/{id}/leave-requests` | any | Congés d'un employé |
| `GET` | `/api/employees/{id}/schedule` | any | Horaire actif |
| `POST` | `/api/employees/{id}/schedule-override` | hr_manager+ | Créer un override |
| `GET` | `/api/work-schedules` | any | Liste des horaires périodiques |
| `POST` | `/api/work-schedules` | hr_manager+ | Créer un horaire |
| `GET` | `/api/work-schedules/{id}` | any | Détail d'un horaire |
| `PUT` | `/api/work-schedules/{id}` | hr_manager+ | Modifier un horaire |
| `DELETE` | `/api/work-schedules/{id}` | super_admin | Supprimer |
| `POST` | `/api/work-schedules/{id}/generate-shifts` | hr_manager+ | Générer des shifts sur une plage |

### JSON Resources

Les réponses API utilisent des **Laravel API Resources** pour contrôler la sérialisation :

- `EmployeeResource` — données employé + relations optionnelles
- `ShiftResource` — shift avec type et statut
- `LeaveRequestResource` — congé avec employé et statut
- `WorkScheduleResource` — horaire avec ses jours
- `DepartmentResource`, `PositionResource`, `SkillResource`

---

## 6. Couche Services, Requests et Policies

### Services (Module Shifts)

La logique métier complexe est extraite dans `app/Modules/Shifts/Services/` :

**`WorkScheduleService`**
- `generateShifts(WorkSchedule $schedule, Carbon $from, Carbon $to, ?int $employeeId)` — génère les shifts pour chaque jour configuré sur une période, en respectant les overrides et en évitant les doublons.

**`ShiftService`**
- Calcul des heures travaillées et heures supplémentaires (en minutes).
- Vérification des conflits de shifts pour un employé.

**`LeaveRequestService`**
- Workflow d'approbation : met à jour le statut, annule les shifts qui chevauchent la période de congé.

### Form Requests (validation)

Chaque opération d'écriture valide les données en entrée via un `FormRequest` :

| Request | Module | Règles clés |
|---------|--------|-------------|
| `StoreShiftRequest` | Shifts | employee_id exists, dates valides, durée > 0 |
| `UpdateShiftRequest` | Shifts | Mêmes règles, champs optionnels |
| `StoreLeaveRequest` | Shifts | dates logiques (start ≤ end), type enum valide |
| `LoginRequest` | Auth | email format, password requis, throttle |
| `ProfileUpdateRequest` | Profil | email unique sauf soi-même |

### Policies (contrôle d'accès API)

**`EmployeePolicy`** protège les opérations CRUD de l'API Employés :

| Action | Rôle minimum |
|--------|--------------|
| `viewAny` / `view` | Authentifié |
| `create` / `update` | `hr_manager` ou `super_admin` |
| `delete` | `super_admin` uniquement |

---

## 7. Authentification et contrôle d'accès

### Rôles (Spatie laravel-permission)

| Rôle | Description | Accès |
|------|-------------|-------|
| `super_admin` | Administrateur global | Tous les modules + API complète |
| `hr_manager` | Responsable RH | Module Shifts complet, lecture autres modules |
| `hr_employee` | Employé | Lecture de ses propres données |

Les rôles sont assignés aux utilisateurs via `User::assignRole()`. Le compte de démonstration `admin@chronosphere.local` possède le rôle `super_admin`.

### Sessions web vs tokens API

- **Web** : sessions Laravel + cookies (protection CSRF).
- **API** : tokens Sanctum sans état (stateless), révocables individuellement via `/api/auth/logout`.

### Middleware web

| Middleware | Rôle |
|-----------|------|
| `auth` | Redirige vers `/login` si non authentifié |
| `EnsureModuleAccess` | Vérifie que l'utilisateur a accès au module demandé |
| `SetUserTimezone` | Applique le fuseau horaire de l'utilisateur à Carbon |
| `SetActiveContext` | Injecte le contexte de navigation actif dans les vues |

---

## 8. Frontend : build Vite et conventions CSS

### Pipeline Vite

```
resources/css/app.css      →  public/build/assets/app-{hash}.css
resources/js/app.js         →  public/build/assets/app-{hash}.js
resources/js/{module}.js    →  public/build/assets/{module}-{hash}.js
```

Le fichier `public/build/manifest.json` est généré par `npm run build`. Les vues Blade utilisent `@vite(['resources/css/app.css', 'resources/js/{module}.js'])`.

> **Important** : si le fichier `public/hot` persiste après arrêt du serveur dev, Laravel tente de servir les assets depuis le serveur Vite mort. Supprimer `public/hot` pour restaurer le mode production.

### Tailwind CSS v4

La configuration est entièrement en CSS (pas de `tailwind.config.js`) :

```css
@import 'tailwindcss';

@custom-variant dark (&:where(.dark, .dark *));

@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --color-primary: #4f46e5;
    /* … */
}
```

### Classes utilitaires globales

Définies dans `@layer components` de `resources/css/app.css` :

| Classe | Effet |
|--------|-------|
| `.card-hover` | `translateY(-3px)` + shadow renforcée au survol, transition 0.18 s |
| `.btn` | Style de base pour boutons (padding, border-radius, transition) |
| `.form-control` | Input standardisé (border, focus-ring, dark mode) |
| `.tr-hover` | Ligne de tableau avec fond hover |
| `.table-wrap` | Wrapper `overflow-x-auto` pour tableaux responsifs |
| `.skeleton` | Animation de chargement squelette |

### Conventions JavaScript

- Chaque module a son fichier JS dédié (`timetable.js`, `shifts.js`, etc.).
- **Alpine.js** gère les interactions légères (modals, recherche en temps réel, filtres).
- **FullCalendar** est instancié via Alpine avec configuration partagée (locale `fr`, timeZone `Europe/Paris`, firstDay `1`).
- Les appels AJAX (stats RH) utilisent `fetch()` avec le token CSRF en header (`X-CSRF-TOKEN`).

---

## 9. Stratégie de tests

### Suite de tests

```bash
php artisan test
# 158 tests Feature, ~0 Unit (logique couverte par Feature tests)
```

Les tests utilisent une base SQLite en mémoire (`:memory:`) via `RefreshDatabase` pour l'isolation et la rapidité.

### Couverture par domaine

| Domaine | Fichier de test | Tests clés |
|---------|-----------------|------------|
| Auth API | `Api/AuthApiTest.php` | Login, logout, token invalide, throttling |
| CRUD Employés | `Api/EmployeeApiTest.php` | Create/read/update/delete, pagination, filtres |
| Contrôle d'accès | `Api/EmployeeRoleAccessTest.php` | Accès refusé selon rôle (403), token manquant (401) |
| Sous-ressources | `Api/EmployeeSubResourceTest.php` | Shifts et congés par employé |
| Horaires | `Api/WorkScheduleApiTest.php` | CRUD, génération de shifts, overrides |
| Auth web | `Auth/AuthenticationTest.php` | Login form, session, remember me |
| Profil | `ProfileTest.php` | Mise à jour, changement de mot de passe |
| Timetable | `Timetable/RoomTest.php` | CRUD salles via web |
| Shifts web | `Shifts/DepartmentTest.php`, `LeaveRequestTest.php` | CRUD web + workflow congés |
| Réservation | `Booking/ReservationTest.php` | Création, approbation, annulation |
| Projet | `Project/ProjectTest.php`, `TaskTest.php` | CRUD projets et tâches, Kanban |

### Patterns utilisés

- **`RefreshDatabase`** : base remise à zéro avant chaque test.
- **`actingAs($user)`** : tests authentifiés sans passer par le formulaire de connexion.
- **Factories** : chaque modèle dispose d'une factory pour générer des données cohérentes.
- **Assertions JSON** : `assertJsonStructure`, `assertJsonFragment` pour valider les réponses API.
- **Assertions HTTP** : `assertStatus(200/201/403/422/404)` systématiques.
