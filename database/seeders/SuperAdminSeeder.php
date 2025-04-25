<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ ایجاد نقش Super Admin (اگر وجود ندارد)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // 2️⃣ ایجاد کاربر Super Admin (اگر وجود ندارد)
        $user = User::firstOrCreate(
            ['email' => 'afshinkhiabani@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('@fshin123'),
            ]
        );

        // 3️⃣ اختصاص نقش Super Admin به این کاربر
        if (!$user->hasRole('super-admin')) {
            $user->assignRole($superAdminRole);
        }

        $this->command->info('✅ کاربر Super Admin با موفقیت ایجاد شد!');
        $this->command->info('📧 ایمیل: admin@example.com');
        $this->command->info('🔑 رمز عبور: password');
    }
}
