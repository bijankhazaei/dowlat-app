<?php

namespace App\Filament\Resources;

use App\Contracts\Enums\EOrderStates;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Customer;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationGroup = 'مدیریت سفارشات';
    protected static ?string $navigationLabel = 'سفارشات';
    protected static ?string $pluralModelLabel = 'سفارشات';
    protected static ?string $label = 'سفارش';
    protected static ?int $navigationSort = 880;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->label('مشتری')
                            ->searchable()
                            ->preload()
                            ->options(function () {
                                return Customer::all()->pluck('name', 'id');
                            })
                            ->disabled()
                            ->required(),
                        Forms\Components\TextInput::make('total_price')
                            ->label('مبلغ')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Textarea::make('address')
                        ->label('آدرس')
                        ->required(),
                    Forms\Components\TextInput::make('postal_code')
                        ->label('کد پستی')
                        ->required(),
                ])->columns(2)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('شناسه')
                    ->searchable()
                    ->sortable(),
                // customer name and status
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('مشتری')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('وضعیت')
                    ->badge()
                    ->color(fn(EOrderStates $state): string => match ($state) {
                        EOrderStates::Pending => 'warning',    // Orange/yellow for attention needed
                        EOrderStates::Processing => 'primary', // Brand color for active processing
                        EOrderStates::Completed => 'success',  // Green for positive completion
                        EOrderStates::Canceled => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('مبلغ')
                    ->money('IRR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ثبت')
                    ->dateTime()
                    ->jalaliDateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('مشاهده'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\JourneysRelationManager::class,
            RelationManagers\PaymentRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
