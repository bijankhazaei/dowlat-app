<?php

namespace App\Contracts\Enums;

enum EOrderStates: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Canceled = 'canceled';


    /**
     * @return array|string[]
     */
    public static function values(): array
    {
        return array_map(function ($item) {
            return $item->value;
        }, self::cases());
    }
    public static function names(): array
    {
        return array_map(function ($item) {
            return $item->name;
        }, self::cases());
    }
}
