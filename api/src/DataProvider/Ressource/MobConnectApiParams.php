<?php

namespace App\DataProvider\Ressource;

class MobConnectApiParams
{
    /**
     * @var string
     */
    private $apiUri;

    /**
     * @var string
     */
    private $apikey;

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $appClientId;

    /**
     * @var string
     */
    private $appClientSecret;

    public function __construct(array $params)
    {
        $this->apiUri = $params['api_uri'];
        $this->clientId = $params['credentials']['client_id'];
        $this->apikey = $params['credentials']['api_key'];
        $this->appClientId = $params['credentials']['app_client_id'];
        $this->appClientSecret = $params['credentials']['app_client_secret'];
    }

    /**
     * Get the value of apiUri.
     */
    public function getApiUri(): string
    {
        return $this->apiUri;
    }

    /**
     * Get the value of apikey.
     */
    public function getApikey(): string
    {
        return $this->apikey;
    }

    /**
     * Get the value of clientId.
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get the value of appClientId.
     */
    public function getAppClientId(): ?string
    {
        return $this->appClientId;
    }

    /**
     * Get the value of appClientSecret.
     */
    public function getAppClientSecret(): ?string
    {
        return $this->appClientSecret;
    }
}
