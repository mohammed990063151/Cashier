<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncLaratrustPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'إضافة صلاحيات ناقصة من config دون حذف المستخدمين';

    public function handle(): int
    {
        $roleConfig = config('laratrust_seeder.role_structure', []);
        $mapPermission = collect(config('laratrust_seeder.permissions_map', []));

        foreach ($roleConfig as $roleName => $modules) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                [
                    'display_name' => ucwords(str_replace('_', ' ', $roleName)),
                    'description' => ucwords(str_replace('_', ' ', $roleName)),
                ]
            );

            $permissionIds = [];
            foreach ($modules as $module => $value) {
                foreach (explode(',', $value) as $perm) {
                    $permissionValue = $mapPermission->get($perm);
                    if (! $permissionValue) {
                        continue;
                    }

                    $permission = Permission::firstOrCreate(
                        ['name' => $permissionValue.'_'.$module],
                        [
                            'display_name' => ucfirst($permissionValue).' '.ucfirst($module),
                            'description' => ucfirst($permissionValue).' '.ucfirst($module),
                        ]
                    );
                    $permissionIds[] = $permission->id;
                }
            }

            $role->permissions()->syncWithoutDetaching($permissionIds);
            $this->info("تم تحديث دور: {$roleName} (".count($permissionIds).' صلاحية)');
        }

        // ضمان وجود سوبر أدمن وربطه بكل الصلاحيات
        $superRole = Role::where('name', 'super_admin')->first();
        $allPermissionIds = Permission::pluck('id')->all();

        $super = User::firstOrCreate(
            ['email' => 'super_admin@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('123456'),
            ]
        );

        if ($superRole) {
            $super->roles()->syncWithoutDetaching([
                $superRole->id => ['user_type' => User::class],
            ]);
        }

        if (! empty($allPermissionIds)) {
            $super->permissions()->syncWithPivotValues($allPermissionIds, ['user_type' => User::class]);
        }

        $this->info('تم ربط super_admin@gmail.com بكل الصلاحيات (كلمة المرور الافتراضية إن كان جديداً: 123456).');

        return self::SUCCESS;
    }
}
