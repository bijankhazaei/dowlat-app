<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPayments extends ListRecords
{
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    // when view this page redirect to OrderResources
    public function mount(): void
    {
        redirect()->route('filament.admin.resources.orders.index');
    }
}
