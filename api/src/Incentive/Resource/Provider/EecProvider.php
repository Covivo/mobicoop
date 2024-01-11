<?php

namespace App\Incentive\Resource\Provider;

use App\Incentive\Interfaces\EecProviderInterface;

abstract class EecProvider implements EecProviderInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $apiUri;

    /**
     * @var string
     */
    private $authenticationUri;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $appSecret;

    public function getName(): string
    {
        return $this->name;
    }

    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    public function getAuthenticationUri(): string
    {
        return $this->authenticationUri;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function geAppId(): string
    {
        return $this->appId;
    }

    public function geAppSecret(): string
    {
        return $this->appSecret;
    }

    protected function _build(array $provider): self
    {
        $this->_setName($provider['name']);
        $this->_setApiUri($provider['api_uri']);
        $this->_setAuthenticationUri($provider['authentication_uri']);
        $this->_setClientId($provider['client_id']);
        $this->_setAppId($provider['app_id']);
        $this->_setAppSecret($provider['app_secret']);

        return $this;
    }

    /**
     * Set the value of name.
     */
    protected function _setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of apiUri.
     */
    protected function _setApiUri(string $apiUri): self
    {
        $this->apiUri = $apiUri;

        return $this;
    }

    /**
     * Set the value of authenticationUri.
     */
    protected function _setAuthenticationUri(string $authenticationUri): self
    {
        $this->authenticationUri = $authenticationUri;

        return $this;
    }

    /**
     * Set the value of clientId.
     */
    protected function _setClientId(string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Set the value of appId.
     */
    protected function _setAppId(string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set the value of appSecret.
     */
    protected function _setAppSecret(string $appSecret): self
    {
        $this->appSecret = $appSecret;

        return $this;
    }
}
