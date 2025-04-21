<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-customer',
            'read-customer',
            'update-customer',
            'delete-customer',
            'disable-customer',

            'upload-lab-test',
            'update-lab-test',

            'add-supplement',
            'update-supplement',
            'delete-supplement',
            'view-supplement',

            'create-order',
            'read-order',
            'update-order',
            'delete-order',
            'view-order',

            'approve-order',
            'cancel-order',
            'complete-order',

            'order-pill-pack',
            'confirm-pill-pack',
            'cancel-pill-pack',
            'send-pill-pack',
            'complete-pill-pack',
            'reminder-pill-pack',

            'upload-invoice',
            'confirm-report',
            'upload-report',
            'view-report',

            'create-user',
            'read-user',
            'update-user',
            'delete-user',
            'disable-user',

            'create-role',
            'read-role',
            'update-role',
            'delete-role',

            'create-permission',
            'read-permission',
            'update-permission',
            'delete-permission',
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
            ]);
        }
    }
}
