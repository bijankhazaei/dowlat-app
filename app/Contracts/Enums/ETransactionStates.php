<?php

namespace App\Contracts\Enums;

enum ETransactionStates: string
{
    case Init = 'init';
    case Pending = 'pending';
    case Success = 'success';
    case Error = 'error';
    case Expired = 'expired';

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
