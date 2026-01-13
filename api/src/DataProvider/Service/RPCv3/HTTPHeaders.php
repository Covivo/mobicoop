<?php

namespace App\DataProvider\Service\RPCv3;

class HTTPHeaders
{
    public static function getHeaders(string $OauthToken): array
    {
        return [
            'Authorization' => 'Bearer '.$OauthToken,
            'Content-Type' => 'application/json',
        ];
    }
}
