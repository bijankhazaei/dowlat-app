<?php

namespace App\Contracts\Enums;

enum EPaymentStates: string
{
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Error = 'error';
    case Cancelled = 'cancelled';

    public static function values()
    {
        return array_map(function ($item) {
            return $item->value;
        }, self::cases());
    }
    public static function names()
    {
        return array_map(function ($item) {
            return $item->name;
        }, self::cases());
    }
}
