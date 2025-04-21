<?php

namespace App\Filament\Resources\CartResource\Pages;

use App\Filament\Resources\CartResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditCart extends EditRecord
{
    protected static string $resource = CartResource::class;

    public function getTitle(): Htmlable|string
    {
        return "مشاهده جزئیات کارت";
    }

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    // Hide the save button
    protected function getFormActions(): array
    {
        return [];
    }
}
