<?php

namespace App\Contracts\Enums;

enum EJourneyTypes: string
{
    case Once = 'once';
    case Multiple = 'multiple';
    case MultipleWithAction = 'multiple-a';


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
