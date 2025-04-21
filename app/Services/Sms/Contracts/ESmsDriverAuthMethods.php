<?php

namespace App\Services\Sms\Contracts;
enum ESmsDriverAuthMethods: string
{
    case None = 'none';
    case Basic = 'basic';
    case Bearer = 'bearer';
    case ApiKey = 'api';
    case AuthorizationToken = 'token';
}
