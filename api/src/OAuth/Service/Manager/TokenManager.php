<?php

namespace App\OAuth\Service\Manager;

use App\OAuth\Event\HttpQueryErrorEvent;
use App\OAuth\Resource\OAuthCredentials;
use App\OAuth\Resource\OAuthToken;
use App\OAuth\Service\Providers\OAuthProvider;
use App\OAuth\Service\ServiceDefinition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

class TokenManager
{
    /**
     * @var OAuthToken
     */
    protected $_OAuthToken;

    /**
     * @var EventDispatcherInterface
     */
    private $_eventDispatcher;

    /**
     * @var array
     */
    private $_servicesDefinition;

    /**
     * @var \stdClass
     */
    private $_service;

    public function __construct(EventDispatcherInterface $eventDispatcher, array $servicesDefinition)
    {
        $this->_eventDispatcher = $eventDispatcher;

        $this->_servicesDefinition = $servicesDefinition;
    }

    public function getOAuthToken(string $service): ?OAuthToken
    {
        if (!ServiceDefinition::isServiceAvailable($this->_servicesDefinition, $service)) {
            return null;
        }

        $this->_service = (object) $this->_servicesDefinition[$service];

        $this->_build();

        return $this->_OAuthToken;
    }

    private function _build(): void
    {
        ServiceDefinition::_checkServiceConfiguration($this->_service);

        if (file_exists($this->_service->file_path)) {
            $this->_setOAuthToken($this->_getOAuthTokenFromPath());
        }

        if ($this->_OAuthToken && !$this->_OAuthToken->hasExpired()) {
            return;
        }

        $tokenFromQuery = $this->_getOAuthTokenFromQuery();

        if (!$tokenFromQuery) {
            return;
        }

        $this->_setOAuthToken($tokenFromQuery);

        if (!file_put_contents($this->_service->file_path, json_encode($this->_OAuthToken))) {
            throw new \Exception('The OAuthToken data could not be written', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function _getOAuthTokenFromPath(): \stdClass
    {
        return json_decode(file_get_contents($this->_service->file_path), true);
    }

    /**
     * @return false|\stdClass
     */
    private function _getOAuthTokenFromQuery()
    {
        try {
            $response = (new OAuthProvider($this->_service->uri, new OAuthCredentials($this->_service->access_key, $this->_service->secret_key)))->postItem();
        } catch (\Throwable $th) {
            $event = new HttpQueryErrorEvent($th);
            $this->_eventDispatcher->dispatch(HttpQueryErrorEvent::NAME, $event);

            return false;
        }

        $response->expiration_date = OAuthToken::getExpirationDateFromExpiresIn($response->expires_in);

        return $response;
    }

    private function _setOAuthToken(\stdClass $json): self
    {
        if (!property_exists($json, 'token') || !property_exists($json, 'expiration_date')) {
            throw new \Exception('Authentication data is invalid', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->_OAuthToken = new OAuthToken($json->token, new \DateTime($json->expiration_date), $json->expires_in);

        return $this;
    }
}
