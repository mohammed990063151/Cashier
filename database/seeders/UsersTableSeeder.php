<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء المستخدم
        $user = User::firstOrCreate(
            ['email' => 'super_admin@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('123456'),
            ]
        );

        // جلب الدور
        $role = Role::firstOrCreate(
            ['name' => 'super_admin'],
            [
                'display_name' => 'Super Admin',
                'description' => 'Super Admin Role',
            ]
        );

        // ربط المستخدم بالدور
       $user->roles()->syncWithoutDetaching([
    $role->id => ['user_type' => User::class]
]);

    }
}
