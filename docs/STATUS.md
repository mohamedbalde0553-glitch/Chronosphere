# ChronoSphere — Suivi du projet

## État global
- Date de démarrage : 2026-05-26
- Phase actuelle : Phase 5 - Structure des dossiers
- Dernière session : 2026-05-26

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

## Phases terminées (suite)

### Phase 3 — Packages Composer
- spatie/laravel-permission 7.4, maatwebsite/excel 3.1, barryvdh/laravel-dompdf 3.1
- intervention/image 4.1, spatie/laravel-activitylog 4.12, spatie/laravel-backup 10.2
- spatie/laravel-translatable 6.14, laravel/telescope 5.20 (dev), laravel/pint (déjà présent)
- Configs publiées : permission, excel, dompdf, telescope
- Fix Composer : preferred-install=dist, github-protocols=https

### Phase 4 — Packages NPM
- alpinejs, @fullcalendar/* (core/daygrid/timegrid/list/interaction)
- frappe-gantt, chart.js, html2canvas, html2pdf.js, sortablejs, lucide, dayjs
- Tailwind v4 : 5 palettes configurées via @theme dans app.css
- Alpine.js initialisé dans app.js
- Build Vite validé (11.7s)

## Phase en cours

Phase 5 - Structure des dossiers

## Phases à venir

- [ ] Phase 4 : Packages NPM
- [ ] Phase 5 : Structure des dossiers
- [ ] Phase 6 : Migrations complètes BDD
- [ ] Phase 7 : Models Eloquent
- [ ] Phase 8 : Authentification + Spatie
- [ ] Phase 9 : Module 0 (Core)
- [ ] Phase 10 : Module 3 (Agenda)
- [ ] Phase 11 : Module 1 (Universitaire)
- [ ] Phase 12 : Module 4 (Réservation)
- [ ] Phase 13 : Module 2 (Employés)
- [ ] Phase 14 : Module 5 (Gantt)
- [ ] Phase 15 : Polish + tests

## Bugs connus / Fixes appliqués
- MySQL 9.1 (WAMP) : clé trop longue → Schema::defaultStringLength(191) + engine InnoDB ROW_FORMAT=DYNAMIC dans config/database.php

## Notes importantes
- APP_URL=http://localhost/chronosphere/public (WAMP)
- MySQL accessible via C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe
