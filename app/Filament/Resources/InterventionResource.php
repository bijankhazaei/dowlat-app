<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InterventionResource\Pages;
use App\Filament\Resources\InterventionResource\RelationManagers;
use App\Models\Intervention;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InterventionResource extends Resource
{
    protected static ?string $model = Intervention::class;

    protected static ?string $navigationGroup = 'مدیریت مشتریان';

    protected static ?string $navigationLabel = 'توصیه‌ها';

    protected static ?string $pluralModelLabel = 'توصیه‌ها';

    protected static ?string $label = 'توصیه';

    protected static ?int $navigationSort = 898;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
            'index' => Pages\ListInterventions::route('/'),
            'create' => Pages\CreateIntervention::route('/create'),
            'edit' => Pages\EditIntervention::route('/{record}/edit'),
        ];
    }
}
