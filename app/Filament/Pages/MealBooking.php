<?php

namespace App\Filament\Pages;

use App\Contracts\Enums\EPaymentStates;
use App\Models\Meal;
use App\Models\MealReservation;
use App\Models\MealReservationItem;
use App\Models\Payment;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Payment\PaymentService;
use Exception;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Shetabit\Multipay\Invoice;
use App\Services\Payment\Facade\Shetabit;


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
        $this->selectedMeals = [];
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
        $current = now();
        $nextSaturday = $current->copy()->next('Saturday');
        $start = $nextSaturday->copy();
        $end = $nextSaturday->copy()->addMonth();

        $current = $start->copy();
        while ($current <= $end) {
            if ($current->isThursday() || $current->isFriday()) {
                $current->addDay();
                continue;
            }

            $dayName = strtolower($current->englishDayOfWeek);
            $persianDayName = verta($current)->format('l');
            $daysPassed = $start->diffInDays($current);
            $weekNumber = (int) floor($daysPassed / 7) % 2 == 0 ? 1 : 2;
            $meals = Meal::where('day_of_week', $dayName)
                ->where('week_number', $weekNumber)
                ->get();
            $this->days->push([
                'dayName' => $persianDayName,
                'date' => $current->format('Y-m-d'),
                'label' => verta($current)->format('Y/m/d'),
                'meals' => $meals,
            ]);

            $current->addDay();
        }
    }

    public function submit()
    {
        DB::beginTransaction();

        try {

            $total = 0;

            $mealReservation = MealReservation::create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'price' => 0
            ]);

            foreach ($this->selectedMeals as $date => $mealIds) {
                foreach ($mealIds as $mealId => $checked) {
                    if (!$checked) continue;

                    $meal = Meal::findOrFail($mealId);

                    MealReservationItem::create([
                        'meal_reservation_id' => $mealReservation->id,
                        'meal_id' => $mealId,
                        'reservation_date' => $date,
                        'price' => $meal->price
                    ]);

                    $total += $meal->price;
                }
            }

            $mealReservation->update(['price' => $total]);

            $payment = PaymentRepository::create([
                'meal_reservation_id' => $mealReservation->id,
                'amount' => $mealReservation->price,
                'summary' => "سفارش " .auth()->user()->first_name. ' در ',
                'state' => EPaymentStates::Unpaid->value
            ]);

            $transaction = $payment->activeTransactionOrCreate([
                'return_url' => route('payment.callback')
            ]);

            DB::commit();

            session()->flash('success', 'رزرو با موفقیت ثبت شد. انتقال به درگاه بانکی');
            return  redirect(route('filament.pay', $transaction));
        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'خطا در ثبت رزرو. لطفاً دوباره تلاش کنید.');
            dd($e);
        }
    }

    protected function storeBookingDetails($transactionId)
    {

    }
}
