<?php

namespace App\Filament\Exports;

use App\Models\MealReservation;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\DatePicker;

class MealReservationExporter extends Exporter
{
    protected static ?string $model = MealReservation::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('شناسه'),
            ExportColumn::make('user.first_name')
                ->label('نام کاربر'),
            ExportColumn::make('user.mobile')
                ->label('موبایل'),
            ExportColumn::make('price')
                ->label('قیمت'),
            ExportColumn::make('status')
                ->label('وضعیت')
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'در انتظار',
                    'completed' => 'تکمیل شده',
                    'cancelled' => 'لغو شده',
                    default => $state,
                }),
            ExportColumn::make('payment.status')
                ->label('وضعیت پرداخت')
                ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                    'unpaid' => 'پرداخت نشده',
                    'paid' => 'پرداخت شده',
                    'error' => 'خطا در پرداخت',
                    default => 'نامشخص',
                }),
            ExportColumn::make('created_at')
                ->label('تاریخ ایجاد'),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            DatePicker::make('date_from')
                ->label('از تاریخ'),
            DatePicker::make('date_to')
                ->label('تا تاریخ'),
        ];
    }

    public function getQuery()
    {
        $query = parent::getQuery();
        
        if ($this->options['date_from']) {
            $query->whereDate('created_at', '>=', $this->options['date_from']);
        }
        
        if ($this->options['date_to']) {
            $query->whereDate('created_at', '<=', $this->options['date_to']);
        }
        
        return $query;
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'خروجی رزرو غذاها با موفقیت تکمیل شد و ' . number_format($export->successful_rows) . ' ردیف صادر شد.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ردیف ناموفق بود.';
        }

        return $body;
    }

    public function getJobQueue(): ?string
    {
        return null; // Process synchronously for immediate download
    }
}