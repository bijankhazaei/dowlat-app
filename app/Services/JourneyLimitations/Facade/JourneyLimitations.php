<?php
namespace App\Services\JourneyLimitations\Facade;

use App\Services\JourneyLimitations\JourneyLimitationsService;


class JourneyLimitations extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return JourneyLimitationsService::class;
    }
}