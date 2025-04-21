<?php

namespace App\Contracts\Enums;

enum EGenders: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

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
