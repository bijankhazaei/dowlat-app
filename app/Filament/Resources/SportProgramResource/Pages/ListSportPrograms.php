<?php

namespace App\Filament\Resources\SportProgramResource\Pages;

use App\Filament\Resources\SportProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSportPrograms extends ListRecords
{
    protected static string $resource = SportProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
