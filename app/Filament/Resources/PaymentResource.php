<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Model;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'پرداخت‌ها';
    protected static ?string $modelLabel = 'پرداخت';
    protected static ?string $pluralModelLabel = 'پرداخت‌ها';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super-admin']);
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),
                TextColumn::make('mealReservation.user.first_name')
                    ->label('کاربر')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->money('IRR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'unpaid',
                        'success' => 'paid',
                        'danger' => 'error',
                    ])
                    ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                        default => 'نامشخص',
                    }),
                TextColumn::make('summary')
                    ->label('خلاصه')
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                    ])
                    ->label('وضعیت'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TransactionsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'view' => Pages\ViewPayment::route('/{record}'),
        ];
    }
}