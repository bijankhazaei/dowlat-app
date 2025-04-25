<?php

namespace App\Filament\Pages;

use App\Models\Meal;
use App\Models\MealReservation;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MealBooking extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'رزرو غذا';
    protected static ?string $title = 'رزرو غذا';
    protected static string $view = 'filament.pages.meal-booking';

    public Collection $days;
    public array $selectedMeals = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->loadMeals();
    }

    public static function canAccess(): bool
    {
        // Check if the user is authenticated and role is student
        return auth()->check() && auth()->user()->hasRole('student');
    }

    private function loadMeals(): void
    {
        $this->days = collect();
        $start = now()->addDay(); // فردا
        $end = now()->addMonth(); // تا یک ماه بعد

        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isFriday()) {
                $current->addDay();
                continue;
            }

            $dayName = strtolower($current->englishDayOfWeek); // مثلاً saturday
            $meals = Meal::where('day_of_week', $dayName)->get();
            $this->days->push([
                'date' => $current->format('Y-m-d'),
                'label' => $current->format('Y/m/d'),
                'meals' => $meals,
            ]);

            $current->addDay();
        }
    }

    public function submit()
    {
        DB::beginTransaction();

        try {
            foreach ($this->selectedMeals as $date => $mealIds) {
                foreach ($mealIds as $mealId => $checked) {
                    if (!$checked) continue;

                    $meal = Meal::findOrFail($mealId);
                    MealReservation::create([
                        'user_id' => Auth::id(),
                        'meal_id' => $meal->id,
                        'reservation_date' => $date,
                        'price' => $meal->price,
                        'status' => 'pending',
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'رزرو با موفقیت ثبت شد.');
            $this->redirect('/');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'خطا در ثبت رزرو. لطفاً دوباره تلاش کنید.');
        }
    }
}
