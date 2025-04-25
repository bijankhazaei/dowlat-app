<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealReservationResource\Pages;
use App\Filament\Resources\MealReservationResource\RelationManagers;
use App\Models\MealReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MealReservationResource extends Resource
{
    protected static ?string $model = MealReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 300;

    protected static ?string $navigationLabel = 'رزروهای غذا';
    protected static ?string $pluralModelLabel  = 'رزروهای غذا';

    protected static ?string $label = 'رزرو';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super-admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListMealReservations::route('/'),
            'create' => Pages\CreateMealReservation::route('/create'),
            'edit' => Pages\EditMealReservation::route('/{record}/edit'),
        ];
    }
}
