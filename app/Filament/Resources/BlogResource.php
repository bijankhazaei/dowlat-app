<?php

namespace App\Filament\Resources;

use App\Contracts\Enums\BlogTypes;
use App\Filament\Resources\BlogResource\Pages;
use App\Filament\Resources\BlogResource\RelationManagers;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'مطالب بلاگ';

    protected static ?string $label = 'مطالب بلاگ';
    protected static ?string $pluralLabel = 'مطالب بلاگ';

    protected static ?string $navigationGroup = 'تنظیمات';

    protected static ?int $navigationSort = 1002;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    -> label('عنوان')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subtitle')
                    ->label('زیر عنوان')
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->label('محتوا')
                    ->required()
                    ->columnSpanFull(),
                // get options from Enum
                Forms\Components\Select::make('type')
                    ->options(BlogTypes::class)
                    ->enum(BlogTypes::class)
                    ->required(),
                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('نوع'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->jalaliDateTime('Y/m/d')
            ])
            ->filters([
                // filter by type
                Tables\Filters\SelectFilter::make('type')
                    ->options(BlogTypes::class)
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
           // RelationManagers\CategoryRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }
}
