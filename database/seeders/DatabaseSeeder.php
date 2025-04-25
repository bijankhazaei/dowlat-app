<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if(env('APP_ENV') !== 'production') {
            $this->call([
                SuperAdminSeeder::class,
                RoleSeeder::class,
                PermissionSeeder::class
            ]);

            Artisan::call('passport:keys',  [
                '--force' => true
            ]);

            Artisan::call('passport:client', [
                    '--personal' => true,
                    '--name' => config('app.name').' Personal Access Client']
            );

            Artisan::call('passport:client', [
                    '--password' => true,
                    '--name' => config('app.name').' Password Grant Client',
                    '--provider' => 'customers']
            );

            Log::log('info', 'Personal Access Client ID: '.User::first()->id);
        }
    }
}
