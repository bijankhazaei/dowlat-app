<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use Rawilk\FilamentPasswordInput\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $pluralModelLabel  = 'کاربران';

    protected static ?string $label = 'مدیر';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('نام')->required(),
            Forms\Components\TextInput::make('email')->label('ایمیل')->email()->required(),

            Password::make('password')
                ->label('رمز عبور'),

            Forms\Components\Select::make('role_id')
            ->label('نقش')
                ->relationship('roles', 'name')
                ->preload()
                ->required(),

            Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                ->label('تصویر پروفایل')
                ->collection('avatar')
                ->image()
                ->imageResizeMode('cover')
                ->imageCropAspectRatio('1:1')
                ->imageResizeTargetWidth('200')
                ->imageResizeTargetHeight('200')
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $table->modifyQueryUsing(function ($query) {
            $query->where('name', '!=', 'Super Admin');
        });
        // load role of this user
        $table->modifyQueryUsing(function ($query) {
            $query->with('roles');
        });
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('نام')->sortable(),
            Tables\Columns\TextColumn::make('email')->label('ایمیل')->sortable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->label('نقش‌ها')
                ->badge()
                ->separator(',')
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
