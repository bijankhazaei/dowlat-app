<?php

namespace App\Services\Sms;

use App\Services\Sms\Contracts\AbstractSmsProvider;
use App\Services\Sms\Contracts\InvalidSmsProviderException;

/*
* @method mixed send(string $text, string $destination, ?string $source = null) Send a message to a single recipient.
* @method mixed sendMany(string $text, array $destinations, ?string $source = null) Send a message to multiple recipients.
* @method bool deliveryCanBeRequested() Check if delivery reports can be requested.
* @method mixed getDelivery(string $identifier) Retrieve the delivery status of a message.
*/
class SmsService
{
    const GETTER_KEY = PHP_EOL . PHP_EOL . PHP_EOL;
    protected ?AbstractSmsProvider $provider = null;

    public function __construct(string|AbstractSmsProvider|null $provider = null)
    {
        if (empty($provider)) {
            $provider = config('sms.default');
        }
        $this->provider($provider);
    }

    public function provider(string|AbstractSmsProvider $provider = self::GETTER_KEY)
    {
        if ($provider === self::GETTER_KEY) {
            return $this->provider;
        }

        if (!is_string($provider) && is_a($provider, AbstractSmsProvider::class)) {
            $this->provider = $provider;
        } else {
            $providers = config('sms.providers');
            if (!isset($providers[strtolower($provider)])) {
                throw new InvalidSmsProviderException("Provider {$provider} is not available.");
            }
            $provider = $providers[strtolower($provider)];
            $cls = $provider['driver'];
            if (empty($cls) || !is_subclass_of($cls, AbstractSmsProvider::class)) {
                throw new InvalidSmsProviderException("Provider {$cls} can not be instantiated due to invalid configuration.");
            }
            $this->provider = new $cls($provider);
        }
        return $this;
    }

    public function __call($method, $args)
    {
        if (!$this->provider) {
            throw new \RuntimeException("Provider must be selected first.");
        }
        if ($this->provider && method_exists($this->provider, $method) && is_callable([$this->provider, $method])) {
            return call_user_func_array([$this->provider, $method], $args);
        }
        throw new \BadMethodCallException("{$method} method not available.");
    }
}
