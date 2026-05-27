# Guide Utilisateur — ChronoSphere

## Table des matières

1. [Connexion et interface principale](#1-connexion-et-interface-principale)
2. [Tableau de bord](#2-tableau-de-bord)
3. [Module Universitaire](#3-module-universitaire)
4. [Module Employés (RH)](#4-module-employes-rh)
5. [Module Agenda](#5-module-agenda)
6. [Module Réservation](#6-module-réservation)
7. [Module Projet / Gantt](#7-module-projet--gantt)
8. [Profil utilisateur](#8-profil-utilisateur)

---

## 1. Connexion et interface principale

### Connexion

Accédez à l'URL de l'application et entrez vos identifiants. En environnement local, les comptes démo sont :

- `admin@chronosphere.local` / `password` — accès complet
- `demo@chronosphere.local` / `password` — accès restreint

### Navigation

L'interface comprend :

- **Sidebar gauche** : navigation entre les modules. Se réduit automatiquement sur les petits écrans et peut être masquée manuellement via le bouton fléché en haut à droite de la sidebar.
- **Topbar** : titre de la page, cloche de notifications, bascule mode sombre/clair, accès au profil.
- **Toasts** : confirmations en bas à droite (vert = succès, rouge = erreur, orange = avertissement).

### Mode sombre

Cliquez sur l'icône lune/soleil dans la topbar. La préférence est enregistrée dans le navigateur.

### Notifications

La cloche dans la topbar affiche le nombre de notifications non lues. Cliquez dessus pour voir la liste, cliquez sur une notification pour la marquer comme lue ou accéder à l'élément concerné.

---

## 2. Tableau de bord

Le tableau de bord affiche les 5 modules sous forme de cartes. Chaque carte indique si vous avez accès au module (selon votre rôle). Cliquez sur **Accéder** pour ouvrir un module.

En bas de page, vos informations de compte sont affichées : rôles assignés, fuseau horaire, langue.

---

## 3. Module Universitaire

Accès : sidebar **Universitaire** ou `/timetable`

### Tableau de bord Timetable

Affiche les KPIs (salles, matières, groupes, enseignants, séances) et les prochaines séances de la semaine en cours.

### Gestion des entités

Chaque entité (Salles, Matières, Groupes, Enseignants) se gère depuis le menu de gauche :

- **Ajouter** : bouton `+ Ajouter` en haut à droite → formulaire dans une modal.
- **Modifier** : cliquer sur `Modifier` dans la ligne du tableau.
- **Supprimer** : cliquer sur `Supprimer` (confirmation requise).

**Salles** : code, nom, type (TD / Amphithéâtre / TP / Labo / Info), capacité, bâtiment, étage, statut actif.

**Matières** : code, nom, coefficient, ECTS, couleur (utilisée dans le planning), description.

**Groupes** : code, nom, niveau académique, année, capacité, groupe parent (sous-groupes).

**Enseignants** : liés à un utilisateur existant, code employé, titre, spécialité, type de contrat.

### Grille des séances

Accès via **Grille des séances** dans le menu.

- **Vues** : semaine (timeGrid), mois (dayGrid), liste.
- **Créer une séance** : cliquer sur une plage horaire dans le calendrier → modal de création.
- **Déplacer** : glisser-déposer une séance vers un autre créneau.
- **Redimensionner** : étirer le bas d'une séance pour modifier la durée.
- **Modifier / Supprimer** : cliquer sur une séance pour ouvrir ses détails.
- **Filtres** : par enseignant, groupe ou salle (sélecteurs en haut de page).
- **Détection de conflits** : lors de la création, si une salle, un enseignant ou un groupe est déjà occupé, un avertissement s'affiche.

---

## 4. Module Employés (RH)

Accès : sidebar **Employés** ou `/shifts`

### Tableau de bord RH

5 KPIs en temps réel (chargement AJAX) :
- Heures travaillées cette semaine
- Heures supplémentaires
- Congés en attente
- Taux d'absentéisme
- Nombre d'horaires périodiques actifs

Graphique des heures par département, top 5 des employés et liste des prochains shifts.

Boutons d'export : **Rapports** (page dédiée), **Export Excel**, **Export PDF**.

### Trombinoscope des employés

- **Rechercher** : champ de recherche par nom (filtrage instantané Alpine.js).
- **Filtrer** : par département ou statut (Actif / Inactif / Suspendu).
- **Voir la fiche** : icône œil sur la carte → page de détail à 4 onglets.
- **Modifier** : icône crayon → modal d'édition.
- **Ajouter** : bouton `+ Ajouter` → modal de création.

### Fiche employé (4 onglets)

**Informations** : données contractuelles (département, poste, contrat, salaire horaire, date d'embauche), compétences avec niveau (1–5 points), statistiques mensuelles (shifts, heures travaillées, heures prévues, congés).

**Planning** : calendrier FullCalendar filtré sur l'employé, même vue que le planning global.

**Congés** : historique de toutes les demandes de congé de l'employé.

**Horaire actif** : jours configurés (heure début/fin, pause, éligibilité HS), barre de progression heures travaillées vs attendues ce mois.

### Planning global

Accès via **Grille de planning** dans la nav rapide.

- Même interface que le Timetable — créer/déplacer/supprimer des shifts.
- Filtres par département et type de shift.

### Horaires périodiques

Modèles de semaine type (ex : "Bureau 9h-17h30 Lun-Ven").

- **Créer** : bouton `+ Nouvel horaire` → modal avec configuration des jours (heure début/fin, pause, multiplicateur HS).
- **Détail** : cliquer sur le nom d'un horaire pour voir les conflits éventuels, générer des shifts et gérer les overrides.
- **Générer les shifts** : depuis la page de détail, sélectionner une plage de dates et cliquer sur **Générer** — les shifts sont créés pour chaque jour configuré, en respectant les overrides individuels.
- **Overrides** : affecter un horaire différent à un employé sur une période donnée (congé maternité, temps partiel temporaire…).

### Demandes de congés

- **Nouvelle demande** : bouton `+ Nouvelle demande` → choisir employé, type, dates, motif.
- **Approuver** : bouton `Approuver` sur une demande `En attente` → les shifts en conflit sont automatiquement annulés.
- **Rejeter** : bouton `Rejeter` → motif du rejet requis.
- Types de congé : Congé payé, RTT, Maladie, Sans solde, Autre.

### Rapports

Filtres : période (semaine / mois / mois dernier / personnalisé), département.

Affiche :
- KPIs globaux : heures totales, heures supplémentaires, nombre d'employés, taux d'absentéisme
- Top 5 des employés par heures travaillées
- Répartition par département

Export **PDF** (dompdf, format A4 paysage) ou **Excel** (Maatwebsite, toutes les données de la période).

---

## 5. Module Agenda

Accès : sidebar **Agenda** ou `/calendar`

- **Vues** : semaine (timeGrid), mois (dayGrid), liste.
- **Créer un événement** : cliquer sur une plage horaire → modal avec titre, calendrier, dates, récurrence, description.
- **Modifier / Supprimer** : cliquer sur un événement existant.
- **Calendriers** : chaque utilisateur peut avoir plusieurs calendriers (ex : Personnel, Professionnel). Couleurs distinctes par calendrier.
- **Récurrence** : les événements récurrents se répètent selon la règle configurée.

---

## 6. Module Réservation

Accès : sidebar **Réservation** ou `/booking`

### Tableau de bord Réservation

KPIs : ressources actives, catégories, réservations de la semaine, en attente d'approbation.

Liste des prochaines réservations et section dédiée aux réservations en attente.

### Ressources

- **Ajouter** : nom, catégorie, lieu, capacité, couleur, mode d'approbation (auto ou manuelle), délai de réservation max.
- **Catégories** : regroupe les ressources par type (Salles de réunion, Équipements, Véhicules…).

### Calendrier des réservations

- Vue globale ou filtrée par ressource.
- **Créer une réservation** : cliquer sur une plage → modal avec titre, ressource, participants, description, récurrence.
- **Statuts** :
  - `En attente` (amber) : en cours de validation
  - `Confirmé` (vert) : approuvé
  - `Annulé` / `Rejeté` (gris / rouge)

### Liste des réservations

Tableau complet avec actions : **Approuver**, **Rejeter**, **Annuler**, **Supprimer**.

---

## 7. Module Projet / Gantt

Accès : sidebar **Projet / Gantt** ou `/project`

### Liste des projets

- **Nouveau projet** : bouton `+ Nouveau projet` → nom, statut, couleur, dates, budget, description.
- **Statuts** : Actif (vert), En pause (amber), Terminé (bleu), Archivé (gris).
- **Barre de progression** : calculée automatiquement depuis les tâches terminées / total.
- Accès au **Kanban** ou au **Gantt** depuis chaque carte projet.

### Vue Kanban

- Colonnes : `À faire`, `En cours`, `En test`, `Terminé`.
- **Créer une tâche** : bouton `+` en bas de colonne.
- **Déplacer** : glisser-déposer entre colonnes (ordre persisté en BDD).
- **Drawer de tâche** : cliquer sur une tâche pour ouvrir les détails :
  - Titre, description, assignée à, priorité (Faible / Normale / Haute / Urgente)
  - Progression (slider 0–100 %)
  - Date d'échéance
  - Commentaires (liste + ajout en temps réel)

### Vue Gantt

- Barres de tâches avec dates de début / fin.
- **Redimensionner** : étirer une barre pour modifier la durée (mise à jour en BDD).
- **Déplacer** : glisser une barre pour décaler les dates.
- **Vues** : Jour, Semaine, Mois.
- Tableau récapitulatif à gauche (nom, dates, progression).

---

## 8. Profil utilisateur

Accès : clic sur votre nom dans la topbar → `/profile`

- **Informations personnelles** : nom, email.
- **Changer le mot de passe** : ancien mot de passe requis.
- **Zone de danger** : suppression du compte (irréversible, confirmation requise).
