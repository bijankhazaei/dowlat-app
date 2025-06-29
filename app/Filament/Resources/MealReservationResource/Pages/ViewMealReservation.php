<?php

namespace App\Filament\Resources\MealReservationResource\Pages;

use App\Filament\Resources\MealReservationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;

class ViewMealReservation extends ViewRecord
{
    protected static string $resource = MealReservationResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);
        
        if (auth()->user()?->hasRole('user') && $this->record->user_id !== auth()->id()) {
            abort(403);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('اطلاعات رزرو')
                    ->schema([
                        TextEntry::make('user.first_name')
                            ->label('نام کاربر'),
                        TextEntry::make('user.mobile')
                            ->label('موبایل'),
                        TextEntry::make('status')
                            ->label('وضعیت')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'در انتظار',
                                'completed' => 'تکمیل شده',
                                'cancelled' => 'لغو شده',
                                default => $state,
                            }),
                        TextEntry::make('price')
                            ->label('قیمت کل')
                            ->money('IRR'),
                        TextEntry::make('created_at')
                            ->label('تاریخ ایجاد')
                            ->dateTime(),
                    ])
                    ->columns(2),
                
                Section::make('اطلاعات پرداخت')
                    ->schema([
                        TextEntry::make('payment.status')
                            ->label('وضعیت پرداخت')
                            ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                                'unpaid' => 'پرداخت نشده',
                                'paid' => 'پرداخت شده',
                                'error' => 'خطا در پرداخت',
                                default => 'نامشخص',
                            }),
                        TextEntry::make('payment.amount')
                            ->label('مبلغ پرداخت')
                            ->money('IRR'),
                        TextEntry::make('payment.summary')
                            ->label('توضیحات'),
                    ])
                    ->columns(2),

                Section::make('آیتم‌های رزرو شده')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('meal.name')
                                    ->label('نام غذا'),
                                TextEntry::make('reservation_date')
                                    ->label('تاریخ رزرو')
                                    ->date(),
                                TextEntry::make('price')
                                    ->label('قیمت')
                                    ->money('IRR'),
                            ])
                            ->columns(3)
                    ])
            ]);
    }
}