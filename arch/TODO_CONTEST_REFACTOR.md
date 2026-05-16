# TODO: Full Contest Refactor (Solo System)

Status: ✅ Plan approved | ⏳ Models complete | 🔄 Seeders next

## Completed ✅

### 1. Backup & List Current State

### 2. Clean Migrations (4 new files created)

### 3. Update Models (Contest, Registration, EventParticipant, new ContestScore/Result)

## In Progress ⏳

### 4. **Fix Seeders**

- [ ] ContestSeeder.php → use Registration → solo contests
- [ ] ScoreSeeder.php → contest_scores table
- [ ] ContestResultSeeder.php → solo results
- [ ] DatabaseSeeder.php → correct order

## Pending

### 5. Test

- [ ] php artisan migrate:fresh --seed
- [ ] Individual seeders

**Next: Reading seeders to fix for new schema.**
