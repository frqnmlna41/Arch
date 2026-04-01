# TODO: Update UserSeeder for Admin status='active'

## Steps:

- [x] Step 1: Edit database/seeders/UserSeeder.php (add status='active', remove is_active)
- [x] Step 2: Run php artisan db:seed --class=RolePermissionSeeder
- [x] Step 3: Run php artisan db:seed --class=UserSeeder
- [x] Step 4: Test login admin@tournament.com / password → /admin/dashboard (seeder complete)
- [x] Step 5: Complete
