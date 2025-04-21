<?php

namespace App\Filament\Resources\JourneyResource\Pages;

use App\Filament\Resources\JourneyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJourney extends EditRecord
{
    protected static string $resource = JourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
