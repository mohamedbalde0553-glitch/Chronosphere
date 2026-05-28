# ChronoSphere — Suivi du projet

## État global
- Date de démarrage : 2026-05-26
- Phase actuelle : **Phase M terminée** (Corrections, génération automatique, jeu de données microfinance)
- Dernière session : 2026-05-28

## Décision stratégique importante
- Application Android native (Java) prévue **après** le web, **uniquement pour Module 2 (Employés/Shifts)**
- Phase F planifiée : API REST Laravel Sanctum pour Module 2 uniquement
- Les API REST des autres modules ne sont **pas** nécessaires
- La logique métier de Module 2 est dans des Services (prête pour les contrôleurs API)

---

## Phases terminées

### Phase M — Corrections, auto-génération timetable, scénario microfinance (2026-05-28)

#### M.1 — Nettoyage fichiers obsolètes
- Suppression `docs/correction.txt`, `docs/mise a jour.txt`, `docs/nouveau.txt`, `docs/sanctum.txt`
  (prompts de phase et notes Android obsolètes)

#### M.2 — Auto-génération des séances sur un semestre entier
- Nouveau `app/Modules/Timetable/Services/TimetableGeneratorService.php`
  - Itère jour par jour entre `semester.start_date` et `semester.end_date`
  - Crée une `CourseSession` pour chaque jour correspondant au `day_of_week` du `TimeSlot`
  - Gestion : doublons (même cours + même heure), conflits de salle, dates exclues (jours fériés)
  - Retourne `{ created, skipped_duplicate, skipped_conflict }`
- `SessionController::generateFromSchedule()` — endpoint `POST /timetable/courses/{course}/generate-sessions`
- Vue `schedule.blade.php` : modal Alpine.js "Générer séances" avec sélecteur cours / créneau / salle + affichage du résultat

#### M.3 — Boutons "← Retour" manquants
- `shifts/planning.blade.php` — lien retour vers `/shifts`
- `shifts/rapports/index.blade.php` — lien retour vers `/shifts`
- `booking/calendar.blade.php` — lien retour vers `/booking`
- `timetable/schedule.blade.php` — lien retour vers `/timetable`

#### M.4 — Carte employé cliquable (trombinoscope)
- `shifts/employees/index.blade.php` : `@click` sur la carte → navigate vers `shifts/employees/{id}`
- Boutons Modifier et Supprimer : `@click.stop` pour ne pas déclencher la navigation

#### M.5 — Corrections de bugs
- `EmployeeController::index()` : `Position::orderBy('name')` → `Position::orderBy('title')` (colonne réelle en BDD)
- Migration `add_day_of_week_to_uni_time_slots` : colonne `day_of_week` absente de la table (présente dans `$fillable` mais jamais créée)
- Migration `add_color_description_to_hr_departments` : colonnes `color` et `description` absentes (500 à la création de département)
- `uni_course_sessions.created_by` NOT NULL sans default : modifié en `NULL DEFAULT NULL` + `created_by` ajouté dans `TimetableGeneratorService`
- Types de congés `MicrofinanceSeeder` : `annual/sick/maternity/unpaid/compassionate` → `conge_paye/maladie/conge_paye/sans_solde/autre`
- Champ congé : `approved_by` → `validated_by` + `validated_at` ajouté (cohérence modèle `LeaveRequest`)
- Mise à jour des 50 enregistrements DB avec les bons types via `DB::table()->update()`

#### M.6 — Seeders de données complètes
- `database/seeders/MicrofinanceSeeder.php` — scénario entreprise microfinance :
  - 10 départements (DG, CREDIT, EPARGNE, COMPTA, RH, IT, COMM, RECOUVR, AUDIT, JURIDIQUE)
  - 22 postes (Directeur Général → Stagiaire, avec `base_hourly_rate`)
  - 4 types de shift (Journée, Matin, Guichet, Nuit) + 10 compétences métier
  - 3 horaires périodiques avec `WorkScheduleDay`
  - 157 employés avec comptes utilisateurs (email: `prenom.nom{N}@microfinance.mf`, mdp: `password`)
  - ~8 348 shifts sur 3 mois passés + 2 semaines à venir (taux présence 88 %)
  - 50 demandes de congé avec statuts variés (approved/rejected/pending/cancelled)

- `database/seeders/AllModulesSeeder.php` — données inter-modules :
  - **Timetable** : Faculté FSEG, année 2025-2026, 2 semestres, 5 niveaux (L1→M2), 6 salles, 8 matières, 12 créneaux (avec `day_of_week`), 5 enseignants, 5 groupes, 8 cours, 128 séances
  - **Booking** : 4 catégories, 7 ressources (Salle Conseil, Toyota Hilux ×2, Vidéoprojecteur…), 28 réservations
  - **Project** : 4 projets (Digitalisation Crédit, Formation Agents 2026, Refonte Site Web, Audit AML), 20 tâches

#### M.7 — Tests complets (tous modules)
Tests effectués en session authentifiée (admin@chronosphere.local) :

| Module | Résultat |
|--------|----------|
| GET toutes les pages (13 routes) | ✅ 200 |
| Congé create / approve / reject | ✅ 201 / 200 / 200 |
| Réservation booking create / approve / cancel | ✅ 201 / 200 / 200 |
| Département create | ✅ 201 |
| Shift update status | ✅ 200 |
| Séance timetable create | ✅ 201 |
| Auto-génération séances (semestre) | ✅ 200 (16 créées, 6 conflits salle ignorés) |
| Séance delete | ✅ 200 |
| Tâche projet create / update | ✅ 201 / 200 |
| Export Excel RH | ✅ 200 (24 Ko XLSX) |
| API REST (login / employees / departments / logout) | ✅ 200 |

**Données de test chargées** : 157 employés, 10 départements, 22 postes, ~8 348 shifts, 50 congés, 128 séances, 28 réservations, 20 tâches.

---

### Phase 1 — Pré-vérifications
- PHP 8.3.14, Composer 2.9.7, Node v24.16.0, npm 11.13.0, MySQL 9.1.0, Git 2.53.0
- Toutes les extensions PHP présentes
- Node.js et MySQL ajoutés au PATH utilisateur

### Phase 2 — Création projet Laravel
- Laravel 13 installé (PHP 8.3, MySQL 9.1)
- BDD `chronosphere` créée (utf8mb4_unicode_ci)
- .env configuré : MySQL, APP_LOCALE=fr, APP_NAME=ChronoSphere
- Fix appliqué : Schema::defaultStringLength(191) + InnoDB ROW_FORMAT=DYNAMIC
- Migrations de base exécutées avec succès (users, cache, jobs)
- Git initialisé — commit initial effectué

### Phase 3 — Packages Composer
- spatie/laravel-permission 7.4, maatwebsite/excel 3.1, barryvdh/laravel-dompdf 3.1
- intervention/image 4.1, spatie/laravel-activitylog 4.12, spatie/laravel-backup 10.2
- spatie/laravel-translatable 6.14, laravel/telescope 5.20 (dev), laravel/pint (déjà présent)
- Configs publiées : permission, excel, dompdf, telescope

### Phase 4 — Packages NPM
- alpinejs, @fullcalendar/* (core/daygrid/timegrid/list/interaction)
- frappe-gantt, chart.js, html2canvas, html2pdf.js, sortablejs, lucide, dayjs
- Tailwind v4 : 5 palettes configurées via @theme dans app.css
- Alpine.js initialisé dans app.js
- Build Vite validé (11.7s)

### Phase 5 — Structure des dossiers
- Architecture modulaire : app/Modules/{Module}/
- Providers, routes, controllers, models, requests, resources enregistrés

### Phase 6 — Migrations complètes BDD
- 25+ tables créées pour tous les modules
- Relations FK validées

### Phase 7 — Models Eloquent
- Models avec relations, casts, et scopes pour tous les modules
- Spatie Permission intégré sur User

### Phase 8 — Authentification + Spatie
- Auth Laravel + Spatie laravel-permission
- Rôles : super_admin, hr_manager, hr_employee
- Seeder de base avec utilisateurs de test

### Phase 9 — Module Core (Dashboard)
- Dashboard principal avec KPIs dynamiques
- Navigation sidebar responsive
- Système de layout x-app-layout

### Phase 10 — Module Timetable (Universitaire)
- CRUD : Rooms, Subjects, Groups, Teachers, Sessions
- Vue FullCalendar (timegrid/week) avec drag-and-drop
- Détection de conflits (salle / enseignant / groupe)
- Export PDF jsPDF, import Excel Maatwebsite

### Phase 11 — Module Calendar (Agenda)
- Gestion multi-calendriers (couleurs personnalisées)
- FullCalendar list/dayGrid/timeGrid
- Événements récurrents, CRUD Alpine.js

### Phase 12 — Module Shifts (RH)
- Employés (trombinoscope), Compétences, Types de shifts
- Planning FullCalendar (timegrid)
- Congés avec workflow d'approbation
- Départements

### Phase 13 — Module Booking (Réservations)
- Ressources et catégories avec disponibilité
- Calendrier FullCalendar pour visualisation
- Workflow : Pending → Approved/Rejected/Cancelled
- Réservations récurrentes

### Phase 14 — Module Project (Kanban + Gantt)
- Kanban SortableJS avec drag-and-drop entre colonnes
- Drawer de tâche (détails, commentaires, assignation)
- Gantt frappe-gantt avec table de récapitulatif
- Progression par tâche (slider 0–100%)

### Phase A — Bugs post-tests
- Fix : `emp.position?.title` (Position model utilise `title`, pas `name`)
- Fix : Timetable — incohérences schéma/modèle corrigées
- Fix : Project — champ `progress` ajouté à la validation `store`
- Fix : `_crud-script.blade.php` — toast dispatching sur save/delete
- Fix : Teachers — badge contrat avec vraies classes CSS dark

### Phase B — Améliorations (Phase B)
- SortableJS Kanban : reorder persisté via API PATCH
- frappe-gantt : mise à jour dates via API PATCH à la fin du drag
- Commentaires de tâches : endpoint dédié + affichage en temps réel
- Booking calendar : légende couleurs par statut + export PDF
- Shifts planning : filtre par département/type de shift

### Phase C — Polish général (terminée le 2026-05-27)

#### C1 — Infrastructure dark mode
- `resources/css/app.css` : `@custom-variant dark (&:where(.dark, .dark *));`
- `resources/views/layouts/app.blade.php` : script anti-flash + toggle dark mode + toast system
- Persistence via `localStorage`

#### C2 — Animations et feedback
- Toast notifications (success/warning/error) sur toutes les actions CRUD
- Loading spinners SVG sur tous les boutons de sauvegarde
- Transitions 200-300ms sur modals et drawers

#### C3 — Dark mode sur tous les modules (22 fichiers)
Fichiers mis à jour :
- `dashboard.blade.php`
- `modules/timetable/index.blade.php` + `schedule.blade.php`
- `modules/timetable/rooms/index.blade.php`
- `modules/timetable/subjects/index.blade.php`
- `modules/timetable/groups/index.blade.php`
- `modules/timetable/teachers/index.blade.php`
- `modules/timetable/_crud-script.blade.php`
- `modules/shifts/index.blade.php`
- `modules/shifts/employees/index.blade.php`
- `modules/shifts/skills/index.blade.php`
- `modules/shifts/departments/index.blade.php`
- `modules/shifts/shift-types/index.blade.php`
- `modules/shifts/leaves/index.blade.php`
- `modules/shifts/planning.blade.php` (FullCalendar — CSS dark overrides)
- `modules/booking/index.blade.php`
- `modules/booking/calendar.blade.php` (FullCalendar — CSS dark overrides)
- `modules/booking/reservations/index.blade.php`
- `modules/booking/resources/index.blade.php`
- `modules/booking/categories/index.blade.php`
- `modules/calendar/index.blade.php` (FullCalendar — CSS dark overrides)
- `modules/project/index.blade.php`
- `modules/project/projects/board.blade.php` (Kanban drawer complet)
- `modules/project/projects/gantt.blade.php` (frappe-gantt CSS dark overrides)

Patterns dark mode appliqués :
- bg-white → `dark:bg-gray-800` (cards/modals), `dark:bg-gray-900` (FullCalendar containers)
- bg-gray-50 (thead) → `dark:bg-gray-700`
- border-gray-200/100 → `dark:border-gray-700`
- text-gray-900 → `dark:text-white`, text-gray-600 → `dark:text-gray-400`
- Inputs/selects : `dark:border-gray-600 dark:bg-gray-700 dark:text-white`
- Badges status : variantes dark `dark:bg-*/30 dark:text-*-300`
- FullCalendar : CSS `!important` overrides via `@push('styles')`
- frappe-gantt : CSS overrides grid-background, row-line, text fills
- Chart.js : détection runtime `document.documentElement.classList.contains('dark')`

---

## Stack technique
- **Backend** : Laravel 11, PHP 8.3, MySQL 9.1
- **Frontend** : Tailwind CSS v4, Alpine.js, Vite
- **Librairies** : FullCalendar v6, frappe-gantt, Chart.js, SortableJS, jsPDF, Maatwebsite Excel
- **Auth** : Laravel Breeze + Spatie laravel-permission
- **Serveur** : WAMP64, APP_URL=http://localhost/chronosphere/public

---

### Phase D — Tests Feature Laravel (commit 57e0356)
- 83/83 tests Feature passent (Timetable, Project, Booking, Shifts, Auth)
- Fix phpunit.xml : `APP_URL=http://localhost` (évite 404 liés au sous-répertoire WAMP)
- Fix RegistrationTest : `Role::create(['name'=>'cal_user'])` dans setUp (Spatie Permission)
- Couverture : CRUD complet, validations, workflows métier (conflit réservation, approbation congé)

### Phase L — Documentation finale (2026-05-27)

#### L.1 — README.md (racine du projet)
- Présentation, prérequis, installation complète pas-à-pas
- Configuration (.env, BDD, rôles, seeders)
- Démarrage serveur de développement
- Aperçu des 5 modules + accès rapide API REST
- Commandes utiles (tests, build, export)

#### L.2 — docs/GUIDE_UTILISATEUR.md
- Connexion et navigation (sidebar, dark mode, notifications)
- Module Universitaire : emplois du temps, salles, matières, enseignants, groupes
- Module Employés (RH) : trombinoscope, planning, congés, horaires périodiques, rapports
- Module Agenda : calendriers personnels, événements récurrents
- Module Réservation : ressources, réservations, workflow approbation
- Module Projet : Kanban, Gantt, tâches, commentaires

#### L.3 — docs/ARCHITECTURE_TECHNIQUE.md
- Stack technique complète (Laravel 11, Tailwind v4, Alpine.js, FullCalendar v6…)
- Architecture modulaire (App/Modules/*, ServiceProviders)
- Schéma BDD (43 tables, préfixes par module)
- API REST Sanctum : endpoints, authentification, rôles
- Couche Services, Form Requests, Policies
- Stratégie de tests (158 Feature tests PHPUnit)
- Build frontend Vite + conventions CSS

**Commit : docs(phase-l): README + GUIDE_UTILISATEUR + ARCHITECTURE_TECHNIQUE**

---

### Phase K — Polish UI/UX Module 2 (2026-05-27)

#### K.1 — Dashboard RH enrichi
- 5ème KPI card "Horaires actifs" (violet) — count des schedules actifs aujourd'hui
- `StatsController` : ajout `schedules_active` dans la réponse JSON
- Nav rapide : "Horaires périodiques" ajouté (avec lien vers `schedules.index`)

#### K.2 — Page de détail employé (`shifts/employees/{employee}`)
- Route `GET /shifts/employees/{employee}` → `shifts.employees.show`
- `EmployeeController::show()` : charge user, department, position, skills, leaves, activeSchedule, expectedMinutes, monthWorked
- Vue `shifts/employees/show.blade.php` : en-tête avec avatar + statut + 4 onglets Alpine.js :
  - **Informations** : données contractuelles + compétences (niveau 1–5) + 4 KPI stats mois
  - **Planning** : FullCalendar timeGridWeek filtré pour l'employé (réutilise le feed existant `?by=employee&id=X`)
  - **Congés** : tableau de toutes les demandes avec type, dates, statut
  - **Horaire actif** : détail des jours configurés + barre progression heures mois / attendues / écart
- Hash URL (#infos, #planning, …) pour navigation directe

#### K.3 — Trombinoscope
- Bouton "Voir fiche" (icône œil, vert) ajouté sur chaque carte employé → lien dynamique Alpine.js

**158/158 tests passent**

---

### Phase J — Horaires périodiques (Work Schedules) (2026-05-27)

#### J.1 — Migrations (3 tables)
- `hr_work_schedules` : id, name, description, start_date, end_date, department_id, created_by, color, is_active, timestamps, softDeletes
- `hr_work_schedule_days` : id, work_schedule_id, day_of_week, start_time, end_time, break_minutes, is_overtime_eligible, multiplier, timestamps
- `hr_employee_schedule_overrides` : id, employee_id, work_schedule_id, override_start_date, override_end_date, reason, timestamps

#### J.2 — Models Eloquent
- `WorkSchedule` : SoftDeletes, scopes `active()` + `forDate()`, relations department/creator/days/overrides
- `WorkScheduleDay` : méthode `workedMinutes()` (gestion passage minuit), `dayLabel()` statique
- `EmployeeScheduleOverride` : relations employee/schedule
- `Employee` : relation `scheduleOverrides()` ajoutée
- `Department` : relation `workSchedules()` ajoutée

#### J.3 — Service WorkScheduleService
- `generateShiftsFromSchedule()` : génère les shifts planifiés pour une plage de dates, respect des overrides, évite les doublons
- `calculateExpectedHours()` : calcule les minutes théoriques sur une période en respectant les overrides
- `detectConflicts()` : détecte les chevauchements d'horaires actifs dans un même département

#### J.4 — Contrôleur web (WorkScheduleController)
- CRUD complet + generateShifts + storeOverride + destroyOverride
- Routes ajoutées dans `routes/modules/shifts.php`

#### J.5 — Vues Blade
- `schedules/index.blade.php` : liste avec filtres, modal de création/édition Alpine.js, gestion des jours
- `schedules/show.blade.php` : détail avec avertissements conflits, génération shifts, gestion overrides

#### J.6 — API REST + Tests
- `WorkScheduleApiController` : CRUD, generate-shifts, employeeSchedule, storeOverride
- Routes dans `routes/api.php` : `apiResource('work-schedules')` + `generate-shifts` + sous-ressources employé
- Fix : paramètres de route (`$work_schedule`) alignés sur le binding `{work_schedule}` de apiResource
- Fix : `WorkScheduleService` — normalisation datetime via Carbon (toDateTimeString) pour cohérence SQLite/MySQL
- `tests/Feature/Api/WorkScheduleApiTest.php` : 26 tests (CRUD, filtres, generate-shifts, employee schedule, overrides, rôles)
- `docs/API_DOCUMENTATION.md` mis à jour avec les 8 nouveaux endpoints work-schedules

#### J.7 — Seeder HrDemoSeeder
- 3 horaires périodiques : Production (Lun-Ven 7h-15h), Bureau/Admin (Lun-Ven 9h-17h30), Ventes (Lun-Sam ×1.25 samedi)
- 3 overrides : EMP-007 congé maternité (Jun-Août 2026), EMP-009 temps partiel (Avr-Jun 2026), EMP-003 formation (Mai 2026)

**158/158 tests passent**

---

### Phase Z — Audit complet + corrections (2026-05-27)

#### Z.1 — Intégrité projet
- composer install : OK
- migrate:status : 57/57 Ran
- route:list : toutes les routes web + API présentes
- config/cache clear : OK
- npm install : OK

#### Z.2 — Modules web
- Routes vérifiées via `route:list` + 132/132 tests Feature passent
- /dashboard, /timetable, /shifts, /calendar, /booking, /projects : routes enregistrées

#### Z.3 — API REST
- `POST /api/auth/login` → token OK (admin@chronosphere.local)
- `GET /api/employees` (avec token) → 15 employés retournés

#### Z.4 — Régressions identifiées

| Criticité | Problème | Statut |
|-----------|----------|--------|
| BLOQUANT | `npm run build` — Can't resolve 'tailwindcss' | Corrigé |
| MAJEUR | `Controller::authorize()` inexistant (Laravel 11) | Corrigé (Phase H) |
| MINEUR | `docs/api.md` → renommer en `API_DOCUMENTATION.md` | Corrigé |

#### Z.5 — Corrections appliquées
- **Build frontend** : suppression `tailwindcss@^3.1.0` + `@tailwindcss/forms` (non utilisés), installation `tailwindcss@^4.3.0` en direct → build OK en 1.60s
- **Controller authorize** : `use AuthorizesRequests` ajouté dans `Controller.php`
- **Renommage doc** : `docs/api.md` → `docs/API_DOCUMENTATION.md`

#### Z.6 — État final post-audit
- 132/132 tests passent
- Build frontend opérationnel
- API REST fonctionnelle et sécurisée par rôles

---

### Phase H — Sécurisation API par rôles (2026-05-27)

- `app/Policies/EmployeePolicy.php` — règles par rôle Spatie
  - `super_admin` : accès total via `before()`
  - `hr_manager` : CRUD complet sur tous les employés
  - `hr_employee` : lecture seule de sa propre fiche (index filtré, view/shifts/leave-requests restreints)
  - Sans rôle RH : 403 sur tout
- `AuthorizesRequests` ajouté au `Controller` de base (retiré par défaut en Laravel 11)
- Policy enregistrée via `Gate::policy()` dans `AppServiceProvider`
- `EmployeeApiController` mis à jour avec `$this->authorize()` sur chaque action
- `EmployeeApiTest` et `EmployeeSubResourceTest` mis à jour : actor avec rôle `hr_manager`
- `tests/Feature/Api/EmployeeRoleAccessTest.php` — 18 tests couvrant les 3 rôles + sans rôle
- **132/132 tests passent**

---

### Phase G — Tests Feature API REST (2026-05-27)

- 31 tests Feature couvrant l'API Module 2 (114 au total, 114/114 passent)
- `tests/Feature/Api/AuthApiTest.php` (7 tests) : login valide/invalide, compte inactif, logout, requête non authentifiée
- `tests/Feature/Api/EmployeeApiTest.php` (17 tests) : CRUD complet, pagination, filtres (dept/status/search), validation, données de référence
- `tests/Feature/Api/EmployeeSubResourceTest.php` (7 tests) : shifts (liste + filtre plage dates + isolation par employé), congés (liste + filtre statut + 404)
- Fix : `assertJsonStructure` adapté à l'enveloppe `data` des API Resources
- Fix : format date MySQL (`2026-06-01+00:00:00`) pour le filtre `inRange`

---

### Phase F — API REST Sanctum Module 2 (2026-05-27)

#### F1 — Infrastructure Sanctum
- `laravel/sanctum` v4.3.2 installé, migration `personal_access_tokens` exécutée
- `HasApiTokens` ajouté sur le modèle `User`
- `routes/api.php` configuré dans `bootstrap/app.php`

#### F2 — Authentification API
- `POST /api/auth/login` → token Bearer + infos user
- `POST /api/auth/logout` → révocation du token courant
- Vérification `is_active` + mise à jour `last_login_at` à la connexion

#### F3 — Endpoints Employés (protégés auth:sanctum)
- `GET /api/employees` — liste paginée (filtres : department_id, status, search)
- `POST /api/employees` — création
- `GET /api/employees/{id}` — détail avec skills
- `PUT /api/employees/{id}` — modification
- `DELETE /api/employees/{id}` — suppression logique (soft delete)
- `GET /api/employees/{id}/shifts` — shifts (filtre from/to)
- `GET /api/employees/{id}/leave-requests` — congés (filtre status)
- `GET /api/departments` — liste des départements
- `GET /api/positions` — liste des postes

#### F4 — API Resources (formatage JSON)
- `EmployeeResource` — champs maîtrisés, user/dept/poste/skills imbriqués via `whenLoaded()`
- `DepartmentResource`, `PositionResource`, `SkillResource` (niveau pivot via `whenPivotLoaded()`)
- `ShiftResource`, `LeaveRequestResource` — dates ISO 8601
- Pagination Laravel : enveloppe `data` + `meta` + `links`

#### F5 — Documentation
- `docs/API_DOCUMENTATION.md` — référence complète des endpoints (exemples body + réponses JSON)

---

### Phase E — Notifications + Rapports + Services Layer (commit f08ca95)

#### E1 — Services Layer (prêt pour Phase F API)
- `ShiftService` : createShift, updateShift, detectConflicts, computeOvertime, checkWeeklyLimit
- `LeaveRequestService` : approve (DB transaction + cascade annulation shifts), reject
- Form Requests : `StoreShiftRequest`, `UpdateShiftRequest`, `StoreLeaveRequest`
- Contrôleurs refactorisés pour injecter les Services

#### E2 — Notifications in-app + email
- Table `notifications` créée (canal database Laravel)
- `ShiftAssigned` : notifie l'employé à la création d'un shift
- `LeaveRequestApproved` : notifie l'employé à l'approbation
- `LeaveRequestRejected` : notifie l'employé au refus (avec motif)
- Cloche de notification dans la topbar (badge unread, dropdown, mark-as-read AJAX)
- Routes : `GET /notifications`, `POST /notifications/read`

#### E3 — Rapports Module 2
- Nouvelle page `/shifts/rapports` : filtres période (semaine/mois/custom) + département
- KPIs : heures travaillées, heures sup, employés actifs, taux absentéisme
- Tableaux : top 5 employés + heures par département
- Export PDF (dompdf, A4 paysage) + Export Excel (maatwebsite)
- Lien "Rapports" ajouté au dashboard RH

---

## Bugs connus / Fixes appliqués
- MySQL 9.1 : clé trop longue → `Schema::defaultStringLength(191)` + `ROW_FORMAT=DYNAMIC`
- Position model : utilise `title`, pas `name` (`emp.position?.title ?? '—'`)
- Timetable : incohérences schéma/modèle corrigées (commit 67bd466)
- Project tasks : champ `progress` requis dans validation `store` (commit 83a0b98)

## Notes importantes
- APP_URL=http://localhost/chronosphere/public (WAMP)
- MySQL accessible via `C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe`
- Dark mode : basculer via le bouton soleil/lune dans la navbar ; état persisté en localStorage
- Rôles Spatie : `super_admin`, `hr_manager`, `hr_employee`
