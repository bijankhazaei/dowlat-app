<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\Builder;
use Rawilk\FilamentPasswordInput\Password;
use function Laravel\Prompts\table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?string $navigationLabel = 'کاربران';
    protected static ?string $pluralModelLabel = 'کاربران';

    protected static ?string $label = 'کاربر';
    protected static ?int $navigationSort = 100;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(['admin', 'super-admin']);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')
                ->label('نام'),
            Forms\Components\TextInput::make('last_name')
                ->label('نام خانوادگی'),
            Forms\Components\TextInput::make('username')
                ->label('نام کاربری')->required()->unique(
                    table: 'users',
                    column: 'username',
                    ignoreRecord: true
                ),
            Forms\Components\TextInput::make('phone')
                ->label('شماره تلفن')->tel()->unique(
                    table: 'users',
                    column: 'phone',
                    ignoreRecord: true
                ),
            Forms\Components\TextInput::make('national_code')
                ->label('کد ملی')
                ->unique(
                    table: 'users',
                    column: 'national_code',
                    ignoreRecord: true
                ),
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
            $query->with('roles');
        });
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('نام')->sortable(),
            Tables\Columns\TextColumn::make('username')->label('نام کاربری')->sortable(),
            Tables\Columns\TextColumn::make('phone')->label('شماره تلفن')->sortable(),
            Tables\Columns\TextColumn::make('national_code')->label('کد ملی')->sortable(),
            Tables\Columns\TextColumn::make('email')->label('ایمیل')->sortable(),
            Tables\Columns\TextColumn::make('roles.name')
                ->label('نقش‌ها')
                ->badge()
                ->separator(',')
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNot(function ($query) {
                $query->role('super-admin');
            });
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
