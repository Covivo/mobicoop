<?php

namespace App\OAuth\Resource;

class OAuthCredentials implements \JsonSerializable
{
    /**
     * @var string
     */
    private $_access_key;

    /**
     * @var string
     */
    private $_secret_key;

    public function __construct(string $access_key, string $secret_key)
    {
        $this->_access_key = $access_key;
        $this->_secret_key = $secret_key;
    }

    public function jsonSerialize(): array
    {
        return [
            'access_key' => $this->_access_key,
            'secret_key' => $this->_secret_key,
        ];
    }
}
