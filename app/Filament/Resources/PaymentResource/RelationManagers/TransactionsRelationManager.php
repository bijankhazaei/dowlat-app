<?php

namespace App\Filament\Resources\PaymentResource\RelationManagers;

use App\Contracts\Enums\EOrderStates;
use App\Contracts\Enums\ETransactionStates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $recordTitleAttribute = 'تراکنش ها';

    public function form(Form $form): Form
    {
        // give details of customer and payment that related to this transaction and just show

        return $form
            ->schema([
            ]);
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(ETransactionStates $state): string => match ($state) {
                        ETransactionStates::Init => 'primary',
                        ETransactionStates::Pending, ETransactionStates::Expired => 'warning',
                        ETransactionStates::Success => 'success',
                        ETransactionStates::Error => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status_message')
                    ->label('شرح وضعیت'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('مبلغ')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ریال '),

                Tables\Columns\TextColumn::make('provider')
                    ->label('سرویس دهنده')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('authority'),

                Tables\Columns\TextColumn::make('reference')
                    ->label('مرجع'),

                Tables\Columns\TextColumn::make('provider_status')
                    ->label('وضعیت سرویس دهنده'),

                Tables\Columns\TextColumn::make('requested_at')
                    ->label('تاریخ درخواست')
                    ->dateTime()
                    ->label('تاریخ ایجاد')->jalaliDateTime(),

                Tables\Columns\TextColumn::make('validated_at')
                    ->label('تاریخ تایید')
                    ->dateTime()
                    ->label('تاریخ ایجاد')->jalaliDateTime(),

                Tables\Columns\TextColumn::make('fee_type'),
            ])
            ->filters([
            ])
            ->headerActions([
            ])
            ->actions([
            ])
            ->bulkActions([
            ]);
    }
}
