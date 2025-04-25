<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Permission;
use App\Filament\Resources\PermissionResource\Pages;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?string $navigationLabel = 'مجوز ها';
    protected static ?string $label = 'مجوز';
    protected static ?int $navigationSort = 9997;
    protected static ?string $navigationIcon = 'heroicon-o-key';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super_admin']);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('نام مجوز')->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('نام مجوز')->sortable(),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    protected function getTableActions(): array
    {
        return []; // No row actions like Edit, View, or Delete
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
        ];
    }
}
