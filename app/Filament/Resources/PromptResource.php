<?php

namespace App\Filament\Resources;

use App\Contracts\Enums\PromptSide;
use App\Filament\Resources\PromptResource\Pages;
use App\Filament\Resources\PromptResource\RelationManagers;
use App\Models\Prompt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PromptResource extends Resource
{
    protected static ?string $model = Prompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'تنظیمات';


    protected static ?int $navigationSort = 1000;

    public static function form(Form $form): Form
    {
        $agentChainMap = [
            'medical_ingest' => ['ingestion'],
            'lifetyle_interven' => ['intervention', 'reflection', 'translation'],
        ];

        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Select::make('agent_name')
                        ->required()
                        ->options([
                            'medical_ingest' => 'Medical Ingest',
                            'lifetyle_interven' => 'Lifestyle Intervention',
                        ])
                        ->reactive()
                        ->afterStateUpdated(fn (callable $set) => $set('chain_name', null)),

                    Forms\Components\Select::make('chain_name')
                        ->required()
                        ->options(function (callable $get) use ($agentChainMap) {
                            $selectedAgent = $get('agent_name');

                            if (!$selectedAgent || !isset($agentChainMap[$selectedAgent])) {
                                return [];
                            }

                            $chains = $agentChainMap[$selectedAgent];
                            return array_combine($chains, $chains);
                        })
                        ->disabled(fn (callable $get) => !$get('agent_name')),

                    Forms\Components\Select::make('side')
                        ->required()
                        ->options(collect(PromptSide::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name])),
                    Forms\Components\TextInput::make('ver')
                        ->required()
                        ->hidden()
                        ->numeric()
                        ->default(0),
                ])->columns(3),

                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Textarea::make('content')
                        ->rows(15)
                        ->required()
                ])->columns(1)
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // create table with form the form columns
                Tables\Columns\TextColumn::make('agent_name')
                    ->label('Agent')
                    ->searchable()
                    ->width('50px')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chain_name')
                    ->label('Chain')
                    ->width('50px')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('side')
                    ->label('Side')
                    ->width('50px')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ver')
                    ->label('Version')
                    ->width('50px')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(100),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('agent_name')
                    ->options([
                        'medical_ingest' => 'Medical Ingest',
                        'lifetyle_interven' => 'Lifestyle Intervention',
                    ]),
                Tables\Filters\SelectFilter::make('chain_name')
                    ->options([
                        'ingestion' => 'Ingestion',
                        'intervention' => 'Intervention',
                        'reflection' => 'Reflection',
                        'translation' => 'Translation',
                    ]),
                Tables\Filters\SelectFilter::make('side')
                    ->options(collect(PromptSide::cases())->mapWithKeys(fn ($case) => [$case->value => $case->name]))
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make()
            ])
            ->searchable(false)
            ->bulkActions([]);
    }

    // Disable the edit page
    public static function canEdit(Model $record): bool
    {
        return false;
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
            'index' => Pages\ListPrompts::route('/'),
            'create' => Pages\CreatePrompt::route('/create'),
        ];
    }
}
