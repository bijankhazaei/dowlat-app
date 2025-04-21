<?php

namespace App\Filament\Resources\CartResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'id';

    // Make the relation manager read-only
    protected static bool $isReadOnly = true;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form fields if needed (won't be used in read-only mode)
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Item ID'),
                Tables\Columns\TextColumn::make('journey.name')
                    ->label('نام جرنی'),
                Tables\Columns\TextColumn::make('quantity')
                ->label('تعداد'),
                Tables\Columns\TextColumn::make('price')
                ->label('قیمت')
                ->formatStateUsing(fn ($state) => number_format($state) . ' ریال '),
                // Add more journey details columns as needed
            ])
            ->filters([
                // Add filters if needed
            ])
            ->headerActions([
                // Remove create action to make it read-only
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Only include view action, remove edit and delete
            ])
            ->bulkActions([
                // Remove bulk actions to make it read-only
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
