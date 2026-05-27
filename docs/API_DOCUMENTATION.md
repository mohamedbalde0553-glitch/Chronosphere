# ChronoSphere — API REST Module 2 (Employés)

Base URL : `http://localhost/chronosphere/public/api`

Toutes les requêtes doivent inclure :
```
Accept: application/json
Content-Type: application/json
```

Les endpoints protégés nécessitent :
```
Authorization: Bearer <token>
```

---

## Authentification

### Login
```
POST /auth/login
```
**Body**
```json
{ "email": "admin@chronosphere.local", "password": "password" }
```
**Réponse 200**
```json
{
  "token": "1|abc123...",
  "user": { "id": 1, "name": "Admin", "email": "admin@chronosphere.local", "avatar": null }
}
```
**Erreurs**
- `422` — identifiants incorrects
- `403` — compte désactivé

---

### Logout
```
POST /auth/logout
Authorization: Bearer <token>
```
**Réponse 200**
```json
{ "message": "Déconnecté." }
```

---

## Données de référence

### Liste des départements
```
GET /departments
Authorization: Bearer <token>
```
**Réponse 200**
```json
[
  { "id": 1, "name": "Informatique", "code": "IT", "color": "#3B82F6" }
]
```

---

### Liste des postes
```
GET /positions
Authorization: Bearer <token>
```
**Réponse 200**
```json
[
  { "id": 1, "title": "Développeur", "base_hourly_rate": "25.00" }
]
```

---

## Employés

### Liste des employés
```
GET /employees
Authorization: Bearer <token>
```
**Paramètres query (optionnels)**

| Paramètre | Type | Description |
|---|---|---|
| `department_id` | integer | Filtrer par département |
| `status` | string | `active` \| `inactive` \| `suspended` |
| `search` | string | Recherche sur nom, email, code employé |
| `per_page` | integer | Résultats par page (défaut : 20) |
| `page` | integer | Numéro de page |

**Réponse 200**
```json
{
  "data": [
    {
      "id": 1,
      "employee_code": "EMP-001",
      "hire_date": "2024-01-15",
      "contract_type": "cdi",
      "status": "active",
      "photo_url": null,
      "weekly_hours_minutes": 2400,
      "max_daily_minutes": null,
      "min_rest_minutes": null,
      "user": { "id": 2, "name": "Jean Dupont", "email": "jean@example.com", "phone": null, "avatar": null },
      "department": { "id": 1, "name": "Informatique", "code": "IT", "color": "#3B82F6" },
      "position": { "id": 1, "title": "Développeur", "base_hourly_rate": "25.00" },
      "created_at": "2026-05-26T13:00:00+00:00",
      "updated_at": "2026-05-26T13:00:00+00:00"
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 20, "total": 15 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

---

### Détail d'un employé
```
GET /employees/{id}
Authorization: Bearer <token>
```
**Réponse 200** — même structure que la liste, avec en plus :
```json
{
  "skills": [
    { "id": 1, "name": "PHP", "category": "backend", "level": 3 }
  ]
}
```
**Erreurs**
- `404` — employé introuvable

---

### Créer un employé
```
POST /employees
Authorization: Bearer <token>
```
**Body**
```json
{
  "user_id": 5,
  "department_id": 1,
  "position_id": 2,
  "employee_code": "EMP-016",
  "hire_date": "2026-06-01",
  "contract_type": "cdi",
  "weekly_hours_minutes": 2400,
  "photo_url": null
}
```
**Valeurs `contract_type`** : `cdi` | `cdd` | `interim` | `freelance`

**Réponse 201** — objet employé complet

**Erreurs**
- `422` — validation échouée (user_id ou employee_code déjà utilisé, champ manquant, etc.)

---

### Modifier un employé
```
PUT /employees/{id}
Authorization: Bearer <token>
```
**Body** (tous les champs requis)
```json
{
  "department_id": 2,
  "position_id": 1,
  "employee_code": "EMP-016",
  "hire_date": "2026-06-01",
  "contract_type": "cdd",
  "weekly_hours_minutes": 1800,
  "status": "active",
  "photo_url": null
}
```
**Valeurs `status`** : `active` | `inactive` | `suspended`

**Réponse 200** — objet employé mis à jour

---

### Supprimer un employé
```
DELETE /employees/{id}
Authorization: Bearer <token>
```
**Réponse 200**
```json
{ "message": "Employé supprimé." }
```
> Suppression logique (soft delete) — l'enregistrement reste en base.

---

## Sous-ressources d'un employé

### Shifts d'un employé
```
GET /employees/{id}/shifts
Authorization: Bearer <token>
```
**Paramètres query (optionnels)**

| Paramètre | Type | Description |
|---|---|---|
| `from` | datetime | Date de début (ISO 8601, ex: `2026-05-01T00:00:00Z`) |
| `to` | datetime | Date de fin (ISO 8601) |
| `per_page` | integer | Résultats par page (défaut : 20) |

**Réponse 200**
```json
{
  "data": [
    {
      "id": 12,
      "employee_id": 1,
      "shift_type": { "id": 1, "name": "Matin", "color": "#10B981" },
      "start_at": "2026-05-26T08:00:00+00:00",
      "end_at": "2026-05-26T16:00:00+00:00",
      "actual_start_at": "2026-05-26T08:05:00+00:00",
      "actual_end_at": "2026-05-26T16:10:00+00:00",
      "worked_minutes": 485,
      "overtime_minutes": 5,
      "break_minutes": 30,
      "status": "completed",
      "notes": null
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
}
```

---

### Congés d'un employé
```
GET /employees/{id}/leave-requests
Authorization: Bearer <token>
```
**Paramètres query (optionnels)**

| Paramètre | Type | Description |
|---|---|---|
| `status` | string | `pending` \| `approved` \| `rejected` \| `cancelled` |
| `per_page` | integer | Résultats par page (défaut : 20) |

**Réponse 200**
```json
{
  "data": [
    {
      "id": 3,
      "employee_id": 1,
      "type": "conge_paye",
      "start_date": "2026-06-10",
      "end_date": "2026-06-14",
      "start_half_day": false,
      "end_half_day": false,
      "reason": "Vacances",
      "status": "approved",
      "rejection_reason": null,
      "validated_at": "2026-05-27T10:00:00+00:00",
      "validator": { "id": 1, "name": "Admin" }
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 1 }
}
```

---

## Codes d'erreur standards

| Code | Signification |
|---|---|
| `401` | Token manquant ou invalide |
| `403` | Compte désactivé |
| `404` | Ressource introuvable |
| `422` | Erreur de validation — body : `{ "message": "...", "errors": { "field": ["..."] } }` |
| `500` | Erreur serveur |
