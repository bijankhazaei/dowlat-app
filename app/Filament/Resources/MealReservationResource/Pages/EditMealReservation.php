<?php

namespace App\Filament\Resources\MealReservationResource\Pages;

use App\Filament\Resources\MealReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Facades\Filament;

class EditMealReservation extends EditRecord
{
    protected static string $resource = MealReservationResource::class;

    public function mount(int | string $record): void
    {
        if (!auth()->user()?->can('edit meal reservations')) {
            abort(403);
        }
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}