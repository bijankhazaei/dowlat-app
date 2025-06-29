<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealReservationResource\Pages;
use App\Models\MealReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class MealReservationResource extends Resource
{
    protected static ?string $model = MealReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'رزرو غذاها';
    protected static ?string $modelLabel = 'رزرو غذا';
    protected static ?string $pluralModelLabel = 'رزرو غذاها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->required()
                    ->label('کاربر'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'در انتظار',
                        'completed' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->required()
                    ->label('وضعیت'),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->label('قیمت'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => 
                auth()->user()?->hasRole('user') 
                    ? $query->where('user_id', auth()->id()) 
                    : $query
            )
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),
                TextColumn::make('user.first_name')
                    ->label('نام کاربر')
                    ->searchable(),
                TextColumn::make('user.mobile')
                    ->label('موبایل')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('قیمت')
                    ->money('IRR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'در انتظار',
                        'completed' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                        default => $state,
                    }),
                BadgeColumn::make('payment.status')
                    ->label('وضعیت پرداخت')
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
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'در انتظار',
                        'completed' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->label('وضعیت'),
                Tables\Filters\SelectFilter::make('payment.status')
                    ->options([
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('payment', fn ($q) => $q->where('status', $data['value']));
                        }
                    })
                    ->label('وضعیت پرداخت'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->can('edit meal reservations')),
            ])
            ->bulkActions([
                // Only allow bulk actions for non-user roles
            ])
            ->headerActions([
                // Only allow header actions for non-user roles  
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMealReservations::route('/'),
            'create' => Pages\CreateMealReservation::route('/create'),
            'view' => Pages\ViewMealReservation::route('/{record}'),
            'edit' => Pages\EditMealReservation::route('/{record}/edit'),
        ];
    }
}