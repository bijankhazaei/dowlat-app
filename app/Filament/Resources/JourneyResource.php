<?php

namespace App\Filament\Resources;

use App\Contracts\Enums\JourneySlug;
use App\Filament\Resources\JourneyResource\Pages;
use App\Filament\Resources\JourneyResource\RelationManagers;
use App\Models\Journey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JourneyResource extends Resource
{
    protected static ?string $model = Journey::class;

    protected static ?string $navigationGroup = 'تنظیمات';
    protected static ?string $navigationLabel = 'جرنی ها';
    protected static ?string $pluralModelLabel = 'جرنی ها';
    protected static ?string $label = 'جرنی';
    protected static ?int $navigationSort = 898;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('نام')
                            ->required(),
                        Forms\Components\Select::make('slug')
                        ->label('نوع جرنی')
                        ->options(collect(JourneySlug::cases())
                        ->mapWithKeys(fn ($case) => [$case->value => $case->name])->toArray()),
                        Forms\Components\TextInput::make('price')
                            ->label('قیمت')
                            ->required()
                            ->mask(
                                RawJs::make(<<<'JS'
            $money($input, '.', ',')
        JS
                                )
                            )
                            ->dehydrateStateUsing(fn($state) => (float)str_replace(',', '', $state)),
                        Forms\Components\Select::make('type')
                            ->label('نوع پردازش')
                            ->options([
                                'once' => 'Once',
                                'multiple' => 'Multiple Without Action',
                                'multiple-a' => 'Multiple With Action',
                            ])
                            ->required(),
                    ])->columns(4),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('interval')
                            ->label('دوره')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('re_buy_interval')
                            ->label('دوره خربد مجدد')
                            ->numeric()
                            ->required(),
                        Forms\Components\Select::make('dependency_id')
                            ->label('انتخاب وابستگی')
                            ->options(Journey::all()->pluck('name', 'id'))
                            ->searchable(),
                    ])->columns(3),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Repeater::make('extra')
                            ->label('افزودنی ها')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('نام')
                                    ->required(),

                                Forms\Components\Select::make('type')
                                    ->label('نوع')
                                    ->options([
                                        'price' => 'قیمت',
                                        'free-form-id' => 'شناسه پرس‌لاین رایگان',
                                        'form-id' => 'شناسه پرس‌لاین',
                                        'other' => 'دیگر',
                                    ])
                                    ->required(),

                                Forms\Components\TextInput::make('value')
                                    ->label('مقدار')
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->createItemButtonLabel('افزودن مورد جدید')
                            ->reorderable()
                            ->collapsible(),
                        Forms\Components\Textarea::make('summary')
                            ->label('خلاصه'),
                        Forms\Components\RichEditor::make('description')
                            ->label('توضیحات'),
                        Forms\Components\SpatieMediaLibraryFileUpload::make('images')
                            ->label('تصاویر')
                            ->collection('images')
                            ->multiple()
                            ->disk('minio-public')
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif'])
                            ->visibility('public')
                    ])->columns(1)



            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('نام'),
                Tables\Columns\TextColumn::make('price')->label('قیمت')
                    ->formatStateUsing(fn($state) => number_format($state) . ' تومان'),
                Tables\Columns\TextColumn::make('type')->label('نوع'),
                Tables\Columns\TextColumn::make('interval')->label('دوره')
                    ->formatStateUsing(fn($state) => $state . ' روز'),
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
            'index' => Pages\ListJourneys::route('/'),
            'create' => Pages\CreateJourney::route('/create'),
            'edit' => Pages\EditJourney::route('/{record}/edit'),
        ];
    }
}
