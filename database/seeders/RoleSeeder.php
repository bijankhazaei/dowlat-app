<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'ops',
                'guard_name' => 'web',
            ],
            [
                'name' => 'medical',
                'guard_name' => 'web',
            ],
            [
                'name' => 'r&d',
                'guard_name' => 'web',
            ],
            [
                'name' => 'marketing',
                'guard_name' => 'web',
            ],
            [
                'name' => 'partners',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
