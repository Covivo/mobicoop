<?php

namespace App\OAuth\Service\Providers;

use App\DataProvider\Service\DataProvider;
use App\OAuth\Resource\OAuthCredentials;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OAuthProvider
{
    public const RESPONSE_SUCCESS = 201;

    public const RESPONSE_ERROR = [401, 404, 500];

    public const RESOURCE_POST = '/auth/token';

    private $_response;

    /**
     * @var string
     */
    private $_uri;

    /**
     * @var \OAuthCredentials
     */
    private $_credentials;

    public function __construct(string $serverUri, OAuthCredentials $credentials)
    {
        $this->_uri = $serverUri;

        $this->_credentials = $credentials;
    }

    public function postItem(): \stdClass
    {
        $dataProvider = new DataProvider($this->_uri, self::RESOURCE_POST);

        $this->_response = $dataProvider->postCollection($this->_credentials);

        return $this->_returnsResponse();
    }

    private function _returnsResponse(): \stdClass
    {
        if (self::RESPONSE_SUCCESS === $this->_response->getCode()) {
            // ! Temporary response
            return json_decode('{"token":  "'.hash('sha256', uniqid(mt_rand(), true)).'", "expires_in": 900}');

            // TODO: Evaluate the response to be returned
            return $this->_response->getValue();
        }

        $this->_throwErrors();
    }

    private function _throwErrors(): void
    {
        if (in_array($this->_response->getCode(), self::RESPONSE_ERROR)) {
            throw new HttpException($this->_response->getCode(), $this->_response->getValue());
        }

        throw new HttpException($this->_response->getCode(), 'The token request query returned an unknowned response.');
    }
}
