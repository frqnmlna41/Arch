# Arch Tournament Management System - Comprehensive Documentation
## Overview
This is a Laravel-based web application for managing martial arts tournaments (e.g., Taekwondo/Pencak Silat). Features admin CRUD for setup, event management, participant registration, scoring, winners, and certificates.

**Tech Stack**:
- Laravel 11+
- Blade Templates + Tailwind CSS
- MySQL/PostgreSQL
- Spatie Permission (roles: admin, coach, athlete, perguruan)
- Sanctum API auth (JSON responses)

**Key Models & Relations** (inferred from migrations/controllers):
```
Sport (1) ←─ Discipline (*)
Sport (1) ←─ AgeCategory (*) ←─ Discipline (*)
Event ←─ Arena, AgeCategory, Participants (Athletes)
EventParticipant (Athlete + Event + Discipline + AgeCategory + Weight)
Match (Contest: Athlete1 vs Athlete2, Event, Discipline, Arena, Status)
Score (Match, Athlete, Judge? → points)
Winner (Event, Discipline, AgeCategory, Athlete, Rank)
Certificate (Winner)
Perguruan (Dojo/School) ←─ Users (Coach) ←─ Athletes
User (roles/status)
```

## Data Flow: End-to-End Process
```
1. AUTH & REGISTRATION
   ├─ Routes: POST /auth/login, /auth/register-perguruan
   ├─ Controller: AuthController
   ├─ Request: LoginRequest, RegisterPerguruanRequest
   ├─ Model: User (status: pending/verified, role: perguruan)
   └─ View/Dashboard: auth/login.blade.php → dashboard/perguruan.blade.php

2. ADMIN SETUP (Prerequisites)
   ├─ Routes: admin/sports*, admin/disciplines*, admin/age-categories*, admin/arenas*
   ├─ Controllers: Admin/{Sport,Discipline,AgeCategory,Arena}Controller (CRUD)
   ├─ Requests: Store/Update*Request (validation w/ custom rules e.g. DisciplineAllowedForAgeCategory)
   ├─ Models: Create records + pivots (discipline_age_category)
   └─ Views: admin/sports/index.blade.php etc. (tables, forms)

3. CREATE EVENT
   ├─ Route: POST /admin/events
   ├─ Controller: Admin/EventController@store
   ├─ Request: StoreEventRequest
   ├─ Model: Event::create()
   └─ View: admin/events/

4. PARTICIPANT REGISTRATION
   ├─ Routes: events/{event}/participants (POST/GET/verify/reject)
   ├─ Controller: EventParticipantController
   ├─ Request: RegisterParticipantRequest (rules: AthleteAgeMatchesCategory, UniqueParticipant)
   ├─ Model: EventParticipant::create(Athlete + Event + Discipline + AgeCat + weight)
   └─ View: N/A (API-driven)

5. GENERATE MATCHES
   ├─ Route: POST /admin/events/{event}/matches/generate
   ├─ Controller: Admin/MatchController@generate
   ├─ Model: Match/Contest (pair participants)
   └─ Assign: PATCH /admin/matches/{match}/arena, /schedule

6. SCHEDULE & LIVE SCORING
   ├─ Routes: /schedule/*, matches/{match}/scores (POST/GET/UPDATE)
   ├─ Controllers: ScheduleController, ScoreController
   ├─ Requests: StoreScoreRequest, UpdateScoreRequest (rules: MatchIsOngoing)
   ├─ Models: Match status=ongoing → Score::create() → calculate
   └─ View: schedule/index.blade.php

7. WINNERS & CERTIFICATES
   ├─ Route: POST /admin/events/{event}/winners/calculate
   ├─ Controller: Admin/WinnerController
   ├─ Request: GenerateWinnerRequest
   ├─ Model: Winner (rank 1-3 per category)
   ├─ Generate: POST /admin/winners/{winner}/certificate → Certificate + PDF (dompdf)
   └─ View: admin/winners/index.blade.php

8. DASHBOARDS
   ├─ Routes: admin/dashboard, dashboard/{role}.blade.php
   └─ Data: Aggregates from models (e.g., active events, pending participants)
```

## Architecture Layers
```
Request (Browser/API)
    ↓ (Routes: web.php - mostly resource + custom)
Controller (e.g. Admin/EventController)
    ↓ (Validation: FormRequest w/ custom Rules)
    ↓ (Auth/Policies: middleware('auth'), $this->authorize(), Policies/*Policy.php)
Model (Eloquent: relations, scopes)
    ↓ (DB: migrations/seeders)
Response (JSON for API + Blade views for admin)
```

## Key Routes (php artisan route:list summary)
- **Admin CRUD**: admin/{sports,disciplines,age-categories,arenas,events,matches,winners}
- **Auth**: POST auth/login/register-perguruan
- **Events**: events/{event}/participants*, /matches/generate
- **Scores**: matches/{match}/scores*
- **Others**: schedule/*, athletes/*

**Admin Prefix**: Most under `admin/*` → Admin\*Controller@index/store/show/update/destroy

## Setup & Run
```
git clone ...
composer install
cp .env.example .env
php artisan key:gen
php artisan migrate --seed
php artisan serve
```
Login: admin@admin.com / password (seeders)

## Views Structure
```
resources/views/admin/* (tables/forms for CRUD)
├── dashboard.blade.php
├── {model}/ (index/create/edit/show)
resources/views/dashboard/{role}.blade.php (perguruan/athlete/coach/admin)
resources/views/layouts/admin.blade.php
components/_table.blade.php etc.
```

## Potential Improvements (from analysis)
- Uncomment auth middleware in Admin controllers.
- English validation messages.
- Full tests.

Full route list: Run `php artisan route:list`.
