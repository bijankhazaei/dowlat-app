<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class SyncPermissionsFromRoutes extends Command
{
    protected $signature = 'permissions:sync';
    protected $description = 'Sync all available routes with the permissions table';

    public function handle()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return $route->getName();
        })->filter();

        $this->info('⏳ در حال همگام‌سازی مجوزها از مسیرها...');

        foreach ($routes as $route) {
            Permission::firstOrCreate(['name' => $route]);
        }

        $this->info('✅ همگام‌سازی مجوزها از مسیرها انجام شد!');
    }
}
