# ChronoSphere — Suivi du projet

## État global
- Date de démarrage : 2026-05-26
- Phase actuelle : Phase 3 - Packages Composer
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

## Phase en cours

Phase 3 - Packages Composer

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
