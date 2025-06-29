<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Hekmatinasser\Verta\Verta;

class MealReservationExcelExport implements FromCollection, WithHeadings
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $reservations = $this->query->with(['user', 'payment', 'items.meal'])->get();
        $rows = new Collection();
        
        foreach ($reservations as $reservation) {
            if ($reservation->items->count() > 0) {
                foreach ($reservation->items as $item) {
                    $rows->push([
                        $reservation->id,
                        $reservation->user->first_name ?? '',
                        $reservation->user->mobile ?? '',
                        $reservation->price,
                        match ($reservation->status) {
                            'pending' => 'در انتظار',
                            'completed' => 'تکمیل شده',
                            'cancelled' => 'لغو شده',
                            default => $reservation->status,
                        },
                        match ($reservation->payment?->status?->value ?? null) {
                            'unpaid' => 'پرداخت نشده',
                            'paid' => 'پرداخت شده',
                            'error' => 'خطا در پرداخت',
                            default => 'نامشخص',
                        },
                        $item->meal->title ?? '',
                        match ($item->meal->meal_type ?? '') {
                            'breakfast' => 'صبحانه',
                            'lunch' => 'ناهار',
                            'dinner' => 'شام',
                            default => $item->meal->meal_type ?? '',
                        },
                        match ($item->meal->day_of_week ?? '') {
                            'saturday' => 'شنبه',
                            'sunday' => 'یکشنبه',
                            'monday' => 'دوشنبه',
                            'tuesday' => 'سه‌شنبه',
                            'wednesday' => 'چهارشنبه',
                            'thursday' => 'پنجشنبه',
                            'friday' => 'جمعه',
                            default => $item->meal->day_of_week ?? '',
                        },
                        $item->reservation_date ? Verta::instance($item->reservation_date)->format('Y/m/d') : '',
                        $item->meal->price ?? '',
                        Verta::instance($reservation->created_at)->format('Y/m/d H:i'),
                    ]);
                }
            } else {
                $rows->push([
                    $reservation->id,
                    $reservation->user->first_name ?? '',
                    $reservation->user->mobile ?? '',
                    $reservation->price,
                    match ($reservation->status) {
                        'pending' => 'در انتظار',
                        'completed' => 'تکمیل شده',
                        'cancelled' => 'لغو شده',
                        default => $reservation->status,
                    },
                    match ($reservation->payment?->status?->value ?? null) {
                        'unpaid' => 'پرداخت نشده',
                        'paid' => 'پرداخت شده',
                        'error' => 'خطا در پرداخت',
                        default => 'نامشخص',
                    },
                    '',
                    '',
                    '',
                    '',
                    '',
                    Verta::instance($reservation->created_at)->format('Y/m/d H:i'),
                ]);
            }
        }
        
        return $rows;
    }

    public function headings(): array
    {
        return [
            'شناسه',
            'نام کاربر',
            'موبایل',
            'قیمت کل',
            'وضعیت',
            'وضعیت پرداخت',
            'نام غذا',
            'نوع غذا',
            'روز هفته',
            'تاریخ رزرو',
            'قیمت غذا',
            'تاریخ ایجاد',
        ];
    }


}