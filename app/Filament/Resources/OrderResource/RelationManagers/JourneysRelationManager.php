<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Filament\Resources\OrderJourneyResource;
use App\Models\Journey;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Table;

class JourneysRelationManager extends RelationManager
{
    protected static string $relationship = 'journeys';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('journey_id')
                    ->label('جرنی')
                    ->options(Journey::pluck('name', 'id'))
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $journey = Journey::find($state);
                            if ($journey) {
                                $startDate = Carbon::now();
                                $endDate = Carbon::now()->addDays($journey->re_buy_interval ?? 0);

                                $set('start_date', $startDate->format('Y-m-d'));
                                $set('end_date', $endDate->format('Y-m-d'));
                            }
                        }
                    })
                    ->required(),

                DatePicker::make('start_date')
                    ->label('تاریخ شروع')
                    ->default(now())
                    ->reactive()
                    ->jalali()
                    ->displayFormat('yy/m/d')
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        if ($state && $get('journey_id')) {
                            $journey = Journey::find($get('journey_id'));
                            if ($journey) {
                                $startDate = Carbon::parse($state);
                                $endDate = $startDate->copy()->addDays($journey->re_buy_interval ?? 0);
                                $set('end_date', $endDate->format('Y-m-d'));
                            }
                        }
                    })
                    ->required(),

                DatePicker::make('end_date')
                    ->label('تاریخ پایان')
                    ->jalali()
                    ->displayFormat('yy/m/d')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('journey.name')
                    ->label('جرنی'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('تاریخ شروع')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('تاریخ پایان')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->dateTime()
                    ->jalaliDateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('افزودن جرنی')
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                ->url(fn($record) => OrderJourneyResource::getUrl('edit', ['record' => $record]))
            ])
            ->bulkActions([
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure dates are properly formatted
        if (isset($data['start_date'])) {
            $data['start_date'] = Carbon::parse($data['start_date'])->format('Y-m-d');
        }

        if (isset($data['end_date'])) {
            $data['end_date'] = Carbon::parse($data['end_date'])->format('Y-m-d');
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If journey_id is set but start_date or end_date are not, calculate them
        if (isset($data['journey_id']) && (!isset($data['start_date']) || !isset($data['end_date']))) {
            $journey = Journey::find($data['journey_id']);
            if ($journey) {
                $data['start_date'] = Carbon::now()->format('Y-m-d');
                $data['end_date'] = Carbon::now()->addDays($journey->rebuy_interval ?? 0)->format('Y-m-d');
            }
        }

        return $data;
    }
}
