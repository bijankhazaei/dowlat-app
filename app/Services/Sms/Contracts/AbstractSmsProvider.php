<?php
namespace App\Services\Sms\Contracts;

use App\Services\Sms\Utils as SmsUtils;
use Illuminate\Support\Facades\Http;

abstract class AbstractSmsProvider
{
    protected array $endpoints;
    protected $settings;

    public function __construct($settings)
    {
        $this->settings = SmsUtils::deep_merge_array_to_object($this->get_default_settings(), $settings, true);
        $this->setEndpoints();
    }
    abstract protected function setEndpoints(): void;

    protected function get_default_settings()
    {
        return [
            'base_url' => '',
            'default_headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'auth' => [
                'type' => ESmsDriverAuthMethods::Bearer,
                'header_name' => 'X-API-Key',
                'token' => null,
                'username' => null,
                'password' => null
            ],
            'default_source' => null
        ];
    }

    protected function obtain_auth_headers()
    {
        $_headers = [];
        $type = is_string($this->settings->auth->type) ? ESmsDriverAuthMethods::from($this->settings->auth->type) : $this->settings->auth->type;
        if ($type == ESmsDriverAuthMethods::Basic && $this->settings->auth->username && $this->settings->auth->password) {
            $_headers['Authorization'] = 'Basic ' . base64_encode($this->settings->auth->username . ':' . $this->settings->auth->password);
        } elseif ($type == ESmsDriverAuthMethods::Bearer && $this->settings->auth->token) {
            $_headers['Authorization'] = 'Bearer ' . $this->settings->auth->token;
        } elseif ($type == ESmsDriverAuthMethods::AuthorizationToken && $this->settings->auth->token) {
            $_headers['Authorization'] = $this->settings->auth->token;
        } elseif ($type == ESmsDriverAuthMethods::ApiKey && $this->settings->auth->token) {
            $_headers[$this->settings->auth->header_name] = $this->settings->auth->token;
        }
        return $_headers;
    }

    protected function build_action_string(string $action, array $attributes = [])
    {
        if (!isset($this->endpoints[$action])) {
            throw new ProviderIncapableException("Current provider is incapable of executing '{$action}'.");
        }

        $search = collect($attributes)->keys()->map(function ($x) {
            return ':' . $x;
        })->toArray();
        $replace = collect($attributes)->values()->toArray();

        return str_replace($search, $replace, $this->endpoints[$action]);
    }

    /**
     * Sends a HTTP Request to the destination server.
     * @param string $action Name of action to be executed. The action must be within methods array.
     * @param array $action_attributes Array of key-value attributes used to replace the url parameters.
     * @param string $method HTTP Request method. POST and GET are supported
     * @param mixed $params HTTP query parameters. Will be passed to http_build_query function if provided.
     * @param mixed $body HTTP request body. Only in POST method.
     * @param array $headers HTTP headers in addition to default headers.
     * @throws \InvalidArgumentException
     */
    protected function http_request(string $action, array $action_attributes = [], string $method = 'POST', $params = null, $body = null, array $headers = [], $no_auth = false)
    {
        $_headers = (array) $this->settings->default_headers;

        if (!$no_auth) {
            $_headers = array_merge($_headers, $this->obtain_auth_headers());
        }

        foreach ($headers as $headerName => $headerValue) {
            $_headers[$headerName] = $headerValue;
        }

        $fullUrl = rtrim($this->settings->base_url, '/') . '/' . ltrim($this->build_action_string($action, $action_attributes), '/');

        try {
            if (!empty($params)) {
                $fullUrl = $fullUrl . '?' . http_build_query($params);
            }

            $req = Http::withHeaders($_headers)->withOptions([
                'verify' => false,
            ]);

            if (strtoupper($method) == 'GET') {
                $response = $req->get($fullUrl);
            } elseif (strtoupper($method) == 'POST') {
                $pass_form_params = false;
                if (!empty($body)) {
                    if ($_headers['Content-Type'] === 'application/json') {
                        $req->withBody(json_encode($body, JSON_THROW_ON_ERROR));
                    } else {
                        $pass_form_params = true;
                        $req->asForm();
                    }
                }
                if ($pass_form_params) {
                    $response = $req->post($fullUrl, ['form_params' => $body]);
                } else {
                    $response = $req->post($fullUrl);
                }
            } else {
                throw new \InvalidArgumentException("HTTP method {$method} is not allowed.");
            }

            return $this->instantiate_response($response->successful() ? 200 : $response->status(), $response->body());
        } catch (\Throwable $e) {
            return $this->instantiate_response(500, $e->getMessage());
        }
    }

    /**
     *  Sends a SOAP Request to the destination server.
     * @param string $action  Name of action to be executed. The action must be within methods array.
     * @param array $action_attributes Array of key-value attributes used to replace the action parameters.
     * @param array $data The Body of the request
     */
    protected function soap_request(string $action, array $action_attributes = [], array $data = [])
    {
        try {
            $context = stream_context_create(array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ),
            ));

            $soapClient = new \SoapClient($this->settings->base_url, ['trace' => 1, 'exception' => 1, 'stream_context' => $context]);
            $result = $soapClient->__soapCall($this->build_action_string($action, $action_attributes), [$data]);

            $response = $this->if_json_decode($result->return);
            try {
                if ($response->error->errorCode < 0) {
                    return $this->instantiate_response($response->error->errorCode, $response);
                }
            } catch (\Throwable $th) {
            }

            return $this->instantiate_response(200, $response);
        } catch (\SoapFault $e) {
            return $this->instantiate_response(400, $e->getMessage());
        } catch (\Throwable $e) {
            return $this->instantiate_response(500, $e->getMessage());
        }
    }

    protected function instantiate_response(int $status, $data)
    {
        $data = $this->if_json_decode($data);
        return (object) ['status' => $status, 'body' => $data, 'provider' => get_class($this), 'body_type' => gettype($data)];
    }

    protected function if_json_decode($entry, ?bool $associative = null, int $depth = 512, int $flags = 0)
    {
        try {
            if (!is_string($entry)) {
                return $entry;
            }
            return json_decode($entry, $associative, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (\Throwable $th) {
            return $entry;
        }
    }

    public abstract function send(string $text, string $destination, ?string $source = null);
    public abstract function sendMany(string $text, array $destinations, ?string $source = null);
    public abstract function deliveryCanBeRequested(): bool;
    public abstract function getDelivery(string $identifier);
}