<?php

namespace Mobicoop\Bundle\MobicoopBundle\Incentive\Entity;

class Incentive implements \JsonSerializable
{
    public const RESOURCE_NAME = 'incentives';

    /**
     * @var null|string
     */
    private $id;

    /**
     * @var null|string
     */
    private $title;

    /**
     * @var null|string
     */
    private $description;

    /**
     * @var null|string
     */
    private $subscriptionLink;

    public function __construct()
    {
    }

    /**
     * Get the value of id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Set the value of id.
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the value of title.
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the value of description.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Set the value of description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of subscriptionLink.
     */
    public function getSubscriptionLink(): ?string
    {
        return $this->subscriptionLink;
    }

    /**
     * Set the value of subscriptionLink.
     */
    public function setSubscriptionLink(?string $subscriptionLink): self
    {
        $this->subscriptionLink = $subscriptionLink;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'subscriptionLink' => $this->getSubscriptionLink(),
        ];
    }
}
