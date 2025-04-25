<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealResource\Pages;
use App\Models\Meal;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MealResource extends Resource
{
    protected static ?string $model = Meal::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 200;

    protected static ?string $navigationLabel = 'غذاها';
    protected static ?string $pluralModelLabel  = 'غذاها';

    protected static ?string $label = 'غذا';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super_admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('day_of_week')
                    ->label('روز هفته')
                    ->options([
                        'saturday' => 'شنبه',
                        'sunday' => 'یک‌شنبه',
                        'monday' => 'دوشنبه',
                        'tuesday' => 'سه‌شنبه',
                        'wednesday' => 'چهارشنبه',
                    ])
                    ->required(),

                Select::make('meal_type')
                    ->label('نوع')
                    ->options([
                        'main' => 'غذای اصلی',
                        'side' => 'پیش‌غذا',
                        'drink' => 'نوشیدنی',
                    ])
                    ->required(),

                TextInput::make('title')
                    ->label('عنوان غذا')
                    ->required()
                    ->maxLength(255),

                TextInput::make('price')
                    ->label('قیمت (تومان)')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('عنوان غذا')->searchable(),
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('روز')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'saturday' => 'شنبه',
                        'sunday' => 'یک‌شنبه',
                        'monday' => 'دوشنبه',
                        'tuesday' => 'سه‌شنبه',
                        'wednesday' => 'چهارشنبه',
                        default => 'نامشخص',
                    }),
                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('روز'),
                Tables\Columns\TextColumn::make('price')->label('قیمت')->money('IRT'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListMeals::route('/'),
            'create' => Pages\CreateMeal::route('/create'),
            'edit' => Pages\EditMeal::route('/{record}/edit'),
        ];
    }
}
