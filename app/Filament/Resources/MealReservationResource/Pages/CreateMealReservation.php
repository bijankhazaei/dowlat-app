<?php

namespace App\Filament\Resources\MealReservationResource\Pages;

use App\Filament\Resources\MealReservationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMealReservation extends CreateRecord
{
    protected static string $resource = MealReservationResource::class;

    public function mount(): void
    {
        if (!auth()->user()?->can('create meal reservations')) {
            abort(403);
        }
        parent::mount();
    }
}