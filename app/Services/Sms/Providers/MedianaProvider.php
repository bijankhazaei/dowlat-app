<?php
namespace App\Services\Sms\Providers;

use App\Services\Sms\Contracts\AbstractSmsProvider;
use App\Services\Sms\Contracts\ProviderIncapableException;

class MedianaProvider extends AbstractSmsProvider
{
    protected function setEndpoints(): void
    {
        $this->endpoints = [
            'send' => 'api/message/send',
            'sendMany' => 'api/message/bulk',
            // 'getDelivery' => 'sms/message/show-recipient/message-id/:bulk_id',
            // 'getUserInfo' => 'getUserInfo',
            // 'pullMessageStatus' => 'sms/report/message-id',
        ];
    }

    public function send(string $text, string $destination, ?string $source = null)
    {
        $source ??= $this->settings->default_source;

        $body = [
            [
                "SourceAddress" => $source,
                "MessageText" => $text,
                "DestinationAddress" => $destination,
            ]
        ];
        return $this->http_request(__FUNCTION__, body: $body);
    }

    public function sendMany(string $text, array $destinations, ?string $source = null)
    {
        $source ??= $this->settings->default_source;

        $body = [
            "SourceAddress" => $source,
            "MessageText" => $text,
            "DestinationAddress" => $destinations,
        ];
        return $this->http_request(__FUNCTION__, body: $body);
    }

    public function deliveryCanBeRequested(): bool
    {
        return false;
    }
    public function getDelivery(string $identifier)
    {
        throw new ProviderIncapableException();
    }
}