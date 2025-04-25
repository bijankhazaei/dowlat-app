<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use App\Filament\Resources\RoleResource\Pages;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?string $navigationLabel = 'نقش ها';
    protected static ?string $pluralLabel = 'نقش ها';
    protected static ?string $modelLabel = 'نقش';
    protected static ?int $navigationSort = 9998;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super_admin']);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        // add multiselect to add permissions to role

        return $form->schema([
            Forms\Components\TextInput::make('name')->label('نام نقش')->required(),
            Forms\Components\Select::make('permissions')
                ->label('دسترسی ها')
                ->multiple()
                ->relationship('permissions', 'name')
                ->preload()
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        // exclude one of them in list
        $table->modifyQueryUsing(function ($query) {
            $query->where('name', '!=', 'super-admin');
        });

        $table->modifyQueryUsing(function ($query) {
            $query->with('permissions');
        });

        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('نام نقش')->sortable(),

        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
