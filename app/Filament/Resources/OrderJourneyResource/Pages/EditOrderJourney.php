<?php

namespace App\Filament\Resources\OrderJourneyResource\Pages;

use App\Filament\Resources\OrderJourneyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderJourney extends EditRecord
{
    protected static string $resource = OrderJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
