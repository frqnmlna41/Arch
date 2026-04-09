:# TODO: Fix Verify/Reject Account Features

Status: 🚀 In Progress (BLACKBOXAI)

## Plan Steps (Priority Order)

### ✅ 1. Create TODO.md [COMPLETED]

- Track progress

### ✅ 2. Fix Registration Flow (AuthController)

- Removed premature role assignment
- Explicit pending status + linked perguruan_id

### ✅ 3. Clean/Fix PerguruanController

- Removed duplicate methods (listActive/update fixed)
- Updated verify(): Edit existing draft Perguruan (no unique validation conflict)
- Fixed reject() logging comment
- Added AuthorizesRequests trait
- JS form field name="name" (view fix)

- Remove duplicate/broken code in listActive()
- Fix return statements

### ⏳ 4. Improve Validation Handling

- Handle duplicate perguruan_name gracefully
- Make fields more flexible

### ⏳ 5. Test JS Frontend

- Ensure form data matches controller expectations
- Verify AJAX success/error handling

### ⏳ 6. Add Status Notifications (Optional)

- Email on verify/reject

### ⏳ 7. Full End-to-End Test

```
php artisan route:clear
php artisan serve
1. Register new perguruan
2. Admin login → verify/reject
3. Test login (active=success, pending=fail)
```

### ⏳ 8. Update TODO_VERIFY_FIX.md

- Mark as ✅ FIXED

## Commands to Run After Fixes

```
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan optimize
```
