<?php

declare(strict_types=1);

namespace App\ExternalJourney\Service;

abstract class JourneyProvider
{
    /**
     * @var int The id of the provider (not useful yet but needed for api)
     */
    private $id;

    /**
     * @var string The name of the provider
     */
    private $name;

    /**
     * @var string The url of the provider
     */
    private $url;

    /**
     * @var string The name of the resource of the provider
     */
    private $resource;

    /**
     * @var string The api key of the provider
     */
    private $apiKey;

    /**
     * @var string The private key of the provider
     */
    private $privateKey;

    /**
     * @var string The protocol of the provider
     */
    private $protocol;

    public function __construct()
    {
        $this->id = 1;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(?string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getPrivateKey(): ?string
    {
        return $this->privateKey;
    }

    public function setPrivateKey(?string $privateKey): self
    {
        $this->privateKey = $privateKey;

        return $this;
    }

    public function getProtocol(): ?string
    {
        return $this->protocol;
    }

    public function setProtocol(?string $protocol): self
    {
        $this->protocol = $protocol;

        return $this;
    }

    abstract public function getJourneys(array $params): array;
}
