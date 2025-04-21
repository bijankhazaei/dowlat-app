<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SportProgramResource\Pages;
use App\Filament\Resources\SportProgramResource\RelationManagers;
use App\Models\SportProgram;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SportProgramResource extends Resource
{
    protected static ?string $model = SportProgram::class;

    protected static ?string $navigationGroup = 'مدیریت مشتریان';

    protected static ?string $navigationLabel = 'برنامه‌های ورزشی';

    protected static ?string $pluralModelLabel = 'برنامه‌های ورزشی';

    protected static ?string $label = 'برنامه ورزشی';

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
            'index' => Pages\ListSportPrograms::route('/'),
            'create' => Pages\CreateSportProgram::route('/create'),
            'edit' => Pages\EditSportProgram::route('/{record}/edit'),
        ];
    }
}
