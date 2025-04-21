<?php

namespace App\Services\Sms\Facade;

use App\Services\Sms\SmsService;

/**
 * Facade for the SMS Service.
 * 
 * @method static mixed send(string $text, string $destination, ?string $source = null) Send a message to a single recipient.
 * @method static mixed sendMany(string $text, array $destinations, ?string $source = null) Send a message to multiple recipients.
 * @method static bool deliveryCanBeRequested() Check if delivery reports can be requested.
 * @method static mixed getDelivery(string $identifier) Retrieve the delivery status of a message.
 */
class Sms extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return SmsService::class;
    }
}