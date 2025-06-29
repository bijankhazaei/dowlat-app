<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MealReservationResource\Pages;
use App\Models\MealReservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Filament\Exports\MealReservationExporter;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MealReservationExcelExport;
use Filament\Forms\Components\DatePicker;

class MealReservationResource extends Resource
{
    protected static ?string $model = MealReservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'رزرو غذاها';
    protected static ?string $modelLabel = 'رزرو غذا';
    protected static ?string $pluralModelLabel = 'رزرو غذاها';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'first_name')
                    ->required()
                    ->label('کاربر')
                    ->visible(fn () => !auth()->user()?->hasRole('student'))
                    ->default(auth()->user()?->hasRole('student') ? auth()->id() : null),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'در انتظار',
                        'completed' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->required()
                    ->label('وضعیت'),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->label('قیمت'),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user && $user->hasRole('student')) {
            return $query->where('user_id', $user->id);
        }
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('شناسه')
                    ->sortable(),
                TextColumn::make('user.first_name')
                    ->label('نام کاربر')
                    ->searchable(),
                TextColumn::make('user.mobile')
                    ->label('موبایل')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('قیمت')
                    ->money('IRR')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('وضعیت')
                    ->colors([
                        'pending' => 'در انتظار',
                        'paid' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'در انتظار',
                        'paid' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                        default => $state,
                    }),
                BadgeColumn::make('payment.status')
                    ->label('وضعیت پرداخت')
                    ->colors([
                        'pending' => 'در انتظار',
                        'paid' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->formatStateUsing(fn ($state): string => match ($state?->value ?? $state) {
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                        default => 'نامشخص',
                    }),
                TextColumn::make('created_at')
                    ->label('تاریخ ایجاد')
                    ->formatStateUsing(fn ($state) => \Hekmatinasser\Verta\Verta::instance($state)->format('Y/m/d H:i'))
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('خروجی Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('از تاریخ')
                            ->displayFormat('Y/m/d')
                            ->jalali(),
                        DatePicker::make('date_to')
                            ->label('تا تاریخ')
                            ->displayFormat('Y/m/d')
                            ->jalali(),
                    ])
                    ->action(function (array $data) {
                        $query = MealReservation::query();
                        if ($data['date_from']) {
                            $query->whereDate('created_at', '>=', $data['date_from']);
                        }
                        if ($data['date_to']) {
                            $query->whereDate('created_at', '<=', $data['date_to']);
                        }
                        return Excel::download(new MealReservationExcelExport($query), 'meal-reservations-' . date('Y-m-d') . '.xlsx');
                    })
                    ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'super-admin']))
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'در انتظار',
                        'paid' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                    ])
                    ->label('وضعیت'),
                Tables\Filters\SelectFilter::make('payment.status')
                    ->options([
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value']) {
                            $query->whereHas('payment', fn ($q) => $q->where('status', $data['value']));
                        }
                    })
                    ->label('وضعیت پرداخت'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => !auth()->user()?->hasRole('student')),
            ])
            ->bulkActions(
                auth()->user()?->hasRole('student') ? [] : [
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                        Tables\Actions\ExportBulkAction::make()
                            ->exporter(MealReservationExporter::class)
                            ->visible(fn () => auth()->user()?->hasAnyRole(['admin', 'super-admin']))
                    ]),
                ]
            )
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        $pages = [
            'index' => Pages\ListMealReservations::route('/'),
            'view' => Pages\ViewMealReservation::route('/{record}'),
        ];

        if (auth()->user()?->hasRole('admin')) {
            $pages['create'] = Pages\CreateMealReservation::route('/create');
            $pages['edit'] = Pages\EditMealReservation::route('/{record}/edit');
        }

        return $pages;
    }
}
