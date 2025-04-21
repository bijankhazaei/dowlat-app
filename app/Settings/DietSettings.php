<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DietSettings extends Settings
{

    public static function group(): string
    {
        return 'default';
    }
}