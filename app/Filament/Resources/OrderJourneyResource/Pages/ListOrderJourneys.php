<?php

namespace App\Filament\Resources\OrderJourneyResource\Pages;

use App\Filament\Resources\OrderJourneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderJourneys extends ListRecords
{
    protected static string $resource = OrderJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
