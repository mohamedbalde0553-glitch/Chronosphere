# ChronoSphere — Suivi du projet

## État global
- Date de démarrage : 2026-05-26
- Phase actuelle : **Phase E terminée** (notifications + rapports + services layer)
- Dernière session : 2026-05-27

## Décision stratégique importante
- Application Android native (Java) prévue **après** le web, **uniquement pour Module 2 (Employés/Shifts)**
- Phase F planifiée : API REST Laravel Sanctum pour Module 2 uniquement
- Les API REST des autres modules ne sont **pas** nécessaires
- La logique métier de Module 2 est dans des Services (prête pour les contrôleurs API)

---

## Phases terminées

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

### Phase E — Notifications + Rapports + Services Layer (commit en cours)

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
