<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\StatsOverviewWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    // Widgets that fill the header area
    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }

    // Widgets that fill the main body
    public function getWidgets(): array
    {
        return [
            // add more custom widgets here...
        ];
    }

    // (Optional) control how many columns wide each widget is
    public function getColumns(): int|string|array
    {
        // e.g. 2 columns on desktop, 1 on mobile:
        return ['sm' => 1, 'lg' => 2];
    }
}
