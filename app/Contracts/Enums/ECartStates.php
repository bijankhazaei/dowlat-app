<?php

namespace App\Contracts\Enums;

enum ECartStates: string
{
    /**
     * Indicates that the cart is open and items can be added into it
     */
    case Open = 'open';
    /**
     * Indicates that there is an order submitted from this cart, even though the order might have any status
     */
    case Ordered = 'ordered';
    /**
     * Indicates that the customer manually cancelled the cart or removed every last item inside it
     */
    case Cancelled = 'cancelled';
    /**
     * Indicates that the customer did not proceed after a certain amount of time
     */
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
