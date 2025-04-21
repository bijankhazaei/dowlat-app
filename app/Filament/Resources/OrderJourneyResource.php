<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJourneyResource\Pages;
use App\Filament\Resources\OrderJourneyResource\RelationManagers;
use App\Models\OrderJourney;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class OrderJourneyResource extends Resource
{
    protected static ?string $model = OrderJourney::class;
    protected static ?string $pluralModelLabel = 'جرنی های سفارش';

    protected static ?string $label = 'جرنی';
    protected static ?string $recordTitleAttribute = 'journey.name';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
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
            RelationManagers\LifeStyleScoresRelationManager::class,
            RelationManagers\LongevityScoresRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderJourneys::route('/'),
            'create' => Pages\CreateOrderJourney::route('/create'),
            'edit' => Pages\EditOrderJourney::route('/{record}/edit'),
        ];
    }
}
