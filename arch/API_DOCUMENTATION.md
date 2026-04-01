  # Arch Tournament CMS API Documentation

Versi Laravel 11 + Sanctum + Spatie Permission.

**Base URL:** `http://localhost:8000/api`

**Auth:** Bearer Token (login first).

## 🔐 Authentication   

All protected endpoints require `Authorization: Bearer {token}`.

### Login

`POST /auth/login`

```json
{
    "email": "admin@example.com",
    "password": "password"
}
```

Response:

```json
{
  "user": { "id":1, "name":"Admin", "roles": [...] },
  "token": "1|abc123..."
}
```

### Register

`POST /auth/register`

```json
{
    "name": "Coach Name",
    "email": "coach@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Default role: coach.

### Me

`GET /auth/me` (auth)
User profile + roles.

### Logout

`POST /auth/logout` (auth)
Revoke current token.

## 🛡️ Admin (role:admin)

### Sports CRUD

`GET/POST/PUT/DELETE /admin/sports`

### Disciplines

`GET/POST/PUT/DELETE /admin/disciplines`

### Age Categories

`GET/POST/PUT/DELETE /admin/age-categories`

### Events

`GET/POST/PUT/DELETE /admin/events`
`PATCH /admin/events/{event}/status` { "status": "ongoing" }

### Matches

`GET/POST/PUT/DELETE /admin/matches`
Custom: `POST /admin/events/{event}/matches/generate`

### Winners

`GET/POST /admin/events/{event}/winners/calculate`
`DELETE /admin/winners/{winner}`

### Certificates

`POST /admin/winners/{winner}/certificate`
`POST /admin/events/{event}/certificates/generate-all`
`GET /certificates/{certificate}/download` (all)

## 👥 Multi-Role

### Athletes

`GET/POST/PUT/DELETE /athletes` (admin full, coach own)

Query: ?search=name&gender=M&per_page=15

### Participants

`GET /events/{event}/participants` (list)
`POST /events/{event}/participants` (register)
`PATCH /events/{event}/participants/{participant}/verify` (admin)

### Scores

`GET /matches/{match}/scores`
`POST /matches/{match}/scores` (judge)
{ "athlete_id":1, "score": 8.5 }

### Schedules

`GET /schedule/` ?event_id=1&date=2024-01-01
`GET /schedule/today`
`GET /schedule/{match}`
`GET /schedule/event/{event}`

## 🚀 Setup

1. composer install
   2
