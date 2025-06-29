<?php

namespace App\Filament\Resources\MealReservationResource\Pages;

use App\Filament\Resources\MealReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMealReservations extends ListRecords
{
    protected static string $resource = MealReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}