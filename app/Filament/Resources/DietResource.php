<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DietResource\Pages;
use App\Models\Diet;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DietResource extends Resource
{
    protected static ?string $model = Diet::class;
    protected static ?string $navigationGroup = 'مدیریت مشتریان';
    protected static ?string $navigationLabel = 'برنامه غذایی';
    protected static ?string $pluralModelLabel = 'برنامه عذایی';
    protected static ?string $label = 'برنامه غذایی';
    protected static ?int $navigationSort = 900;
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
            'index' => Pages\ListDiets::route('/'),
            'create' => Pages\CreateDiet::route('/create'),
            'edit' => Pages\EditDiet::route('/{record}/edit'),
        ];
    }
}
