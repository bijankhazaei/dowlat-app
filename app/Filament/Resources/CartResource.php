<?php

namespace App\Filament\Resources;

use App\Contracts\Enums\ECartStates;
use App\Filament\Resources\CartResource\Pages;
use App\Filament\Resources\CartResource\RelationManagers;
use App\Models\Cart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationGroup = 'مدیریت سفارشات';

    protected static ?string $label = 'سبدهای خرید';
    protected static ?string $pluralLabel = 'سبدهای خرید';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        // show customer details first_name last_name and mobile just as text not input
        return $form->schema([
            Forms\Components\Section::make('مشخصات مشتری')
                ->description('مشخصات مشتری')
                ->schema([
                    Forms\Components\Placeholder::make('customer_first_name')
                        ->label('نام مشتری :')
                        ->content(fn ($record) => $record->customer->first_name ?? 'N/A'),
                    Forms\Components\Placeholder::make('customer_last_name')
                        ->label('نام خانوادگی مشتری :')
                        ->content(fn ($record) => $record->customer->last_name ?? 'N/A'),
                    Forms\Components\Placeholder::make('customer_mobile')
                        ->label('شماره موبایل مشتری: ')
                        ->content(fn ($record) => $record->customer->mobile ?? 'N/A'),
                ])->columns(3)
        ]);
    }

    public static function table(Table $table): Table
    {
        $table->modifyQueryUsing(function (Builder $query) {
            $query->with('customer');
        });

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('شناسه'),
                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label(' نام مشتری'),
                Tables\Columns\TextColumn::make('customer.last_name')
                    ->label('نام خانوادگی مشتری'),
                // Display cart status
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (ECartStates $state): string => match ($state) {
                        ECartStates::Open => 'warning',
                        ECartStates::Ordered => 'success',
                        ECartStates::Cancelled => 'danger',
                        default => 'gray',
                    })
                    ->label('وضعیت'),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('تعداد اقلام'),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('مجموع قیمت')
                    ->formatStateUsing(fn (int $state): string => number_format($state) . ' ریال'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('تاریخ ایجاد')->jalaliDateTime(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('customer');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCarts::route('/'),
            'create' => Pages\CreateCart::route('/create'),
            'edit' => Pages\EditCart::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
