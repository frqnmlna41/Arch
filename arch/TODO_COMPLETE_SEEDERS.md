## TODO: Complete All Table Seeders for Tournament CMS

✅ **PLAN APPROVED BY USER** - Data volume: 1 demo event, 4 categories, 24 athletes, full bracket matches/scores/results.

### Steps to Complete:

#### 1. CREATE MISSING SEEDERS (12 total) ✅

- [x] PerguruanSeeder.php (after UserSeeder)
- [x] EventCategorySeeder.php (after Event/Athlete)
- [x] EventParticipantSeeder.php (after EventCategory)
- [x] RegistrationSeeder.php (after EventParticipant)
- [x] InvoiceSeeder.php (after Registration)
- [x] InvoiceItemSeeder.php (after Invoice)
- [x] ContestSeeder.php (after EventParticipant - generate brackets)
- [x] ScoreSeeder.php (after Contest)
- [x] ContestResultSeeder.php (after Score)
- [x] WinnerSeeder.php (after ContestResult)
- [x] CertificateSeeder.php (after Winner)

**ALL SEEDERS CREATED ✓**

#### 2. UPDATE DatabaseSeeder.php

- [ ] Add calls 10-18 after AthleteSeeder (dependency order)
- [ ] Update progress counter [19/19]
- [ ] Add new demo accounts summary if needed

#### 3. TEST & VERIFY

- [ ] `php artisan migrate:fresh --seed`
- [ ] Check counts: `php artisan tinker` → EventParticipant::count(), Contest::count() etc.
- [ ] Test relations: Athlete::find(1)->eventParticipants()->with('contest')->get()
- [ ] Fix any FK constraint errors

#### 4. CLEANUP

- [ ] Remove this TODO.md
- [ ] Update README.md with seed instructions

**Progress: 0/19**  
**Run: `php artisan db:seed` after all steps**
