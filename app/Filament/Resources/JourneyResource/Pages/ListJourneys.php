<?php

namespace App\Filament\Resources\JourneyResource\Pages;

use App\Filament\Resources\JourneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJourneys extends ListRecords
{
    protected static string $resource = JourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
