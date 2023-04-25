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
    private $shortDistanceSubscriptionId;

    /**
     * @var string
     */
    private $appClientId;

    /**
     * @var string
     */
    private $appClientSecret;

    /**
     * @var string
     */
    private $longDistanceSubscriptionId;

    public function __construct(array $params)
    {
        $this->apiUri = $params['api_uri'];
        $this->clientId = $params['credentials']['client_id'];
        $this->apikey = $params['credentials']['api_key'];
        $this->appClientId = $params['credentials']['app_client_id'];
        $this->appClientSecret = $params['credentials']['app_client_secret'];

        $this->shortDistanceSubscriptionId = $params['subscription_ids']['short_distance'];
        $this->longDistanceSubscriptionId = $params['subscription_ids']['long_distance'];
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
     * Get the value of shortDistanceSubscriptionId.
     */
    public function getShortDistanceSubscriptionId(): string
    {
        return $this->shortDistanceSubscriptionId;
    }

    /**
     * Get the value of longDistanceSubscriptionId.
     */
    public function getLongDistanceSubscriptionId(): string
    {
        return $this->longDistanceSubscriptionId;
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
