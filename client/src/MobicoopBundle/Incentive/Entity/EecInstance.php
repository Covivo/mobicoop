<?php

namespace Mobicoop\Bundle\MobicoopBundle\Incentive\Entity;

// The EEC service status for the instance
class EecInstance implements \JsonSerializable
{
    public const RESOURCE_NAME = 'instance';
    public const RESOURCE_PATH = 'eec/'.self::RESOURCE_NAME;

    /**
     * @var bool
     */
    private $available = false;

    /**
     * @var null|\DateTimeInterface
     */
    private $expirationDate;

    public function jsonSerialize(): array
    {
        return [
            'available' => $this->isAvailable(),
            'expirationDate' => null,
        ];
    }

    /**
     * Get the value of available.
     */
    public function getAvailable(): bool
    {
        return $this->available;
    }

    public function isAvailable(): bool
    {
        return $this->getAvailable();
    }

    /**
     * Set the value of available.
     */
    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    /**
     * Get the value of expirationDate.
     */
    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    /**
     * Set the value of expirationDate.
     */
    public function setExpirationDate(\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
