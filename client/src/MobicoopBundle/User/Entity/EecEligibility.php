<?php

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

class EecEligibility implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of the User
     */
    private $id;

    /**
     * @var int
     */
    private $longDistanceJourneysNumber;

    /**
     * @var bool
     */
    private $longDistanceEligibility;

    /**
     * @var int
     */
    private $shortDistanceJourneysNumber;

    /**
     * @var bool
     */
    private $shortDistanceEligibility;

    public function jsonSerialize()
    {
        return [];
    }

    /**
     * Get the id of the User.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of longDistanceJourneysNumber.
     */
    public function getLongDistanceJourneysNumber(): int
    {
        return $this->longDistanceJourneysNumber;
    }

    /**
     * Set the value of longDistanceJourneysNumber.
     */
    public function setLongDistanceJourneysNumber(int $longDistanceJourneysNumber): self
    {
        $this->longDistanceJourneysNumber = $longDistanceJourneysNumber;

        return $this;
    }

    /**
     * Get the value of longDistanceEligibility.
     */
    public function getLongDistanceEligibility(): bool
    {
        return $this->longDistanceEligibility;
    }

    /**
     * Set the value of longDistanceEligibility.
     */
    public function setLongDistanceEligibility(bool $longDistanceEligibility): self
    {
        $this->longDistanceEligibility = $longDistanceEligibility;

        return $this;
    }

    /**
     * Get the value of shortDistanceJourneysNumber.
     */
    public function getShortDistanceJourneysNumber(): int
    {
        return $this->shortDistanceJourneysNumber;
    }

    /**
     * Set the value of shortDistanceJourneysNumber.
     */
    public function setShortDistanceJourneysNumber(int $shortDistanceJourneysNumber): self
    {
        $this->shortDistanceJourneysNumber = $shortDistanceJourneysNumber;

        return $this;
    }

    /**
     * Get the value of shortDistanceEligibility.
     */
    public function getShortDistanceEligibility(): bool
    {
        return $this->shortDistanceEligibility;
    }

    /**
     * Set the value of shortDistanceEligibility.
     */
    public function setShortDistanceEligibility(bool $shortDistanceEligibility): self
    {
        $this->shortDistanceEligibility = $shortDistanceEligibility;

        return $this;
    }
}
