<?php

namespace App\Contracts\Enums;

enum JourneySlug: string
{
    case LIFE_STYLE_SCORE = 'slug-style-score';
    case LONGEVITY_SCORE = 'longevity-score';
    case INTERVENTION = 'interaction';
    case PILL_PACK = 'pill-pack';
    case DIET = 'diet';
    case SPORT_PROGRAM = 'sport-program';


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
