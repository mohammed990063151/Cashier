<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class LaratrustSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Truncating User, Role and Permission tables...');
        $this->truncateLaratrustTables();

        $roleConfig = config('laratrust_seeder.role_structure', []);
        $userPermissions = config('laratrust_seeder.permission_structure', []);
        $mapPermission = collect(config('laratrust_seeder.permissions_map', []));

        foreach ($roleConfig as $roleName => $modules) {

            // إنشاء الدور
            $role = Role::create([
                'name' => $roleName,
                'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                'description' => ucwords(str_replace('_', ' ', $roleName)),
            ]);

            // إنشاء الصلاحيات وربطها بالدور
            $permissions = [];
            foreach ($modules as $module => $value) {
                foreach (explode(',', $value) as $perm) {
                    $permissionValue = $mapPermission->get($perm);
                    $permissions[] = Permission::firstOrCreate([
                        'name' => $permissionValue . '_' . $module,
                        'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                    ])->id;
                }
            }
            $role->permissions()->sync($permissions);

            // إنشاء مستخدم افتراضي للدور
            $this->command->info("Creating '{$roleName}' user...");
            $user = User::create([
                'first_name' => ucwords(str_replace('_', ' ', $roleName)),
                'last_name' => 'User',
                'email' => $roleName . '@app.com',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]);

            // ربط المستخدم بالدور
           // ربط المستخدمين المخصصين بالصلاحيات
$user->permissions()->syncWithPivotValues($permissions, ['user_type' => User::class]);


        }

        // إنشاء مستخدمين مع صلاحيات مخصصة
        foreach ($userPermissions as $username => $modules) {
            $user = User::create([
                'first_name' => ucwords(str_replace('_', ' ', $username)),
                'last_name' => 'User',
                'email' => $username . '@app.com',
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ]);

            $permissions = [];
            foreach ($modules as $module => $value) {
                foreach (explode(',', $value) as $perm) {
                    $permissionValue = $mapPermission->get($perm);
                    $permissions[] = Permission::firstOrCreate([
                        'name' => $permissionValue . '_' . $module,
                        'display_name' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                        'description' => ucfirst($permissionValue) . ' ' . ucfirst($module),
                    ])->id;
                }
            }
            $user->permissions()->syncWithPivotValues($permissions, ['user_type' => User::class]);

        }
    }

    public function truncateLaratrustTables()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('permission_role')->truncate();
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        User::truncate();
        Role::truncate();
        Permission::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
