<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use App\Models\Customer;
use App\Filament\Resources\CustomerResource\Pages;
use Illuminate\Support\Facades\Log;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $navigationGroup = 'مدیریت مشتریان';
    protected static ?string $modelLabel = 'مشتری';
    protected static ?string $pluralModelLabel = 'مشتریان';
    protected static ?string $navigationLabel = 'مشتریان';
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 700;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('first_name')
                ->label('نام')
                ->required(),
            Forms\Components\TextInput::make('last_name')
                ->label('نام خانوادگی')
                ->required(),
            Forms\Components\TextInput::make('mobile')
                ->label('شماره موبایل')
                ->required(),
            Forms\Components\TextInput::make('address')
                ->label('آدرس'),
            Forms\Components\TextInput::make('postal_code')
                ->label('کد پستی'),
            Forms\Components\TextInput::make('email')
                ->label('ایمیل'),
            Forms\Components\Select::make('gender')
                ->label('جنسیت')
                ->options([
                    'male' => 'مرد',
                    'female' => 'زن',
                ]),
            Forms\Components\DatePicker::make('birthdate')
                ->label('تاریخ تولد')->jalali(),
            Forms\Components\TextInput::make('national_code')
                ->label('کد ملی'),
            Forms\Components\Toggle::make('is_registered')
                ->label('ثبت‌نام شده'),
            Forms\Components\SpatieMediaLibraryFileUpload::make('avatar')
                ->label('تصویر پروفایل')
                ->collection('avatar')
                ->disk('minio-public')
                ->imageEditor()
                ->maxSize(5120)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                ->maxFiles(1)
                ->panelAspectRatio('2:1')
                ->panelLayout('integrated')
                ->downloadable()
                ->openable()
                ->visibility('public')
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $table->modifyQueryUsing(function ($query) {
            $query->with('media');
        });

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('نام')->sortable(),
                Tables\Columns\TextColumn::make('last_name')->label('نام خانوادگی')->sortable(),
                Tables\Columns\TextColumn::make('mobile')->label('شماره موبایل'),
                Tables\Columns\BooleanColumn::make('is_registered')->label('ثبت‌نام شده'),
                Tables\Columns\SpatieMediaLibraryImageColumn::make('avatar')->label('تصویر پروفایل')
                    ->collection('avatar')
                    ->disk('minio-public')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-avatar.jpg'))
                    ->extraImgAttributes(['class' => 'h-12 w-12 rounded-full object-cover shadow-sm']),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('registered')
                    ->query(fn ($query) => $query->where('is_registered', true))
                    ->label('ثبت‌نام‌شده‌ها'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
