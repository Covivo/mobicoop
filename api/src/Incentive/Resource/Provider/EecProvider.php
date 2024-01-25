<?php

namespace App\Incentive\Resource\Provider;

use App\Incentive\Interfaces\EecProviderInterface;

abstract class EecProvider implements EecProviderInterface
{
    /**
     * @var null|string
     */
    private $name;

    /**
     * @var null|string
     */
    private $apiUri;

    /**
     * @var null|string
     */
    private $authenticationUri;

    /**
     * @var null|bool
     */
    private $autoCreateAccount = false;

    /**
     * @var null|string
     */
    private $clientId;

    /**
     * @var null|string
     */
    private $clientSecret;

    /**
     * @var null|string
     */
    private $codeVerifier;

    /**
     * @var null|string
     */
    private $logoutRedirectUri;

    /**
     * @var null|string
     */
    private $appId;

    /**
     * @var null|string
     */
    private $appSecret;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    public function getAuthenticationUri(): ?string
    {
        return $this->authenticationUri;
    }

    public function getAutoCreateAccount(): ?bool
    {
        return $this->autoCreateAccount;
    }

    public function getClientId(): ?string
    {
        return $this->clientId;
    }

    public function getClientSecret(): ?string
    {
        return !is_null($this->clientSecret) ? $this->clientSecret : '';
    }

    public function getCodeVerifier(): ?string
    {
        return $this->codeVerifier;
    }

    public function getAppId(): ?string
    {
        return $this->appId;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function getLogoutRedirectUri(): ?string
    {
        return !is_null($this->logoutRedirectUri) ? $this->logoutRedirectUri : '';
    }

    protected function _build(array $provider): self
    {
        $this->_setName($provider['name']);
        $this->_setApiUri($provider['api_uri']);
        $this->_setAuthenticationUri($provider['authentication_uri']);
        $this->_setAutoCreateAccount($provider['auto_create_account']);
        $this->_setClientId($provider['client_id']);
        $this->_setClientSecret($provider['client_secret']);
        $this->_setCodeVerifier($provider['code_verifier']);
        $this->_setLogoutRedirectUri($provider['logout_redirect_uri']);
        $this->_setAppId($provider['app_id']);
        $this->_setAppSecret($provider['app_secret']);

        return $this;
    }

    /**
     * Set the value of name.
     */
    protected function _setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of apiUri.
     */
    protected function _setApiUri(?string $apiUri): self
    {
        $this->apiUri = $apiUri;

        return $this;
    }

    /**
     * Set the value of authenticationUri.
     */
    protected function _setAuthenticationUri(?string $authenticationUri): self
    {
        $this->authenticationUri = $authenticationUri;

        return $this;
    }

    protected function _setAutoCreateAccount(?bool $autoCreateAccount): self
    {
        $this->autoCreateAccount = $autoCreateAccount;

        return $this;
    }

    /**
     * Set the value of clientId.
     */
    protected function _setClientId(?string $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Set the value of clientSecret.
     */
    protected function _setClientSecret(?string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;

        return $this;
    }

    protected function _setCodeVerifier(?string $code): self
    {
        $this->codeVerifier = $code;

        return $this;
    }

    protected function _setLogoutRedirectUri(?string $uri)
    {
        $this->logoutRedirectUri = $uri;
    }

    /**
     * Set the value of appId.
     */
    protected function _setAppId(?string $appId): self
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Set the value of appSecret.
     */
    protected function _setAppSecret(?string $appSecret): self
    {
        $this->appSecret = $appSecret;

        return $this;
    }
}
