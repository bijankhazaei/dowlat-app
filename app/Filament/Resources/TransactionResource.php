<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'تراکنش‌ها';
    protected static ?string $modelLabel = 'تراکنش';
    protected static ?string $pluralModelLabel = 'تراکنش‌ها';

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
                TextColumn::make('payment.mealReservation.user.first_name')
                    ->label('کاربر')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('مبلغ')
                    ->money('IRR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'secondary' => 'init',
                        'warning' => 'pending',
                        'success' => 'success',
                        'danger' => 'error',
                    ])
                    ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                        'init' => 'آماده',
                        'pending' => 'در انتظار',
                        'success' => 'موفق',
                        'error' => 'خطا',
                        default => 'نامشخص',
                    }),
                TextColumn::make('provider')
                    ->label('درگاه'),
                TextColumn::make('authority')
                    ->label('کد پیگیری')
                    ->limit(20),
                TextColumn::make('reference')
                    ->label('شماره مرجع')
                    ->limit(20),
                TextColumn::make('status_message')
                    ->label('پیام وضعیت')
                    ->limit(30),
                TextColumn::make('requested_at')
                    ->label('زمان درخواست')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('validated_at')
                    ->label('زمان تایید')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'init' => 'آماده',
                        'pending' => 'در انتظار',
                        'success' => 'موفق',
                        'error' => 'خطا',
                    ])
                    ->label('وضعیت'),
                Tables\Filters\SelectFilter::make('provider')
                    ->options([
                        'zarinpal' => 'زرین‌پال',
                        'mellat' => 'ملت',
                        'parsian' => 'پارسیان',
                    ])
                    ->label('درگاه'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}