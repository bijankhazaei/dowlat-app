<?php

namespace App\Contracts\Enums;

enum ETransactionFeeTypes: string
{
    case Inclusive = 'inclusive';
    case ExclusiveOntoCustomer = 'exclusive-customer';
    case ExclusiveOntoAccountHolder = 'exclusive-account-holder';

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
