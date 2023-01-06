<?php

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

class CeeSubscription implements ResourceInterface, \JsonSerializable
{
    public const RESOURCE_NAME = 'my_cee_subscriptions';

    /**
     * @var int The id of the User
     */
    private $id;

    /**
     * @var array the short distance journeys
     */
    private $shortDistanceSubscriptions;

    /**
     * @var array the long distance journeys
     */
    private $longDistanceSubscriptions;

    /**
     * @var int Nb pending class C proofs
     */
    private $nbPendingProofs;

    /**
     * @var int Nb validated class C proofs
     */
    private $nbValidatedProofs;

    /**
     * @var int Nb rejected class C proofs
     */
    private $nbRejectedProofs;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getShortDistanceSubscriptions(): ?array
    {
        return $this->shortDistanceSubscriptions;
    }

    public function setShortDistanceSubscriptions(?array $shortDistanceSubscriptions)
    {
        $this->shortDistanceSubscriptions = $shortDistanceSubscriptions;

        return $this;
    }

    public function getLongDistanceSubscriptions(): ?array
    {
        return $this->longDistanceSubscriptions;
    }

    public function setLongDistanceSubscriptions(?array $longDistanceSubscriptions)
    {
        $this->longDistanceSubscriptions = $longDistanceSubscriptions;

        return $this;
    }

    public function getNbPendingProofs(): ?int
    {
        return $this->nbPendingProofs;
    }

    public function setNbPendingProofs(int $nbPendingProofs): self
    {
        $this->nbPendingProofs = $nbPendingProofs;

        return $this;
    }

    public function getNbValidatedProofs(): ?int
    {
        return $this->nbValidatedProofs;
    }

    public function setNbValidatedProofs(int $nbValidatedProofs): self
    {
        $this->nbValidatedProofs = $nbValidatedProofs;

        return $this;
    }

    public function getNbRejectedProofs(): ?int
    {
        return $this->nbRejectedProofs;
    }

    public function setNbRejectedProofs(int $nbRejectedProofs): self
    {
        $this->nbRejectedProofs = $nbRejectedProofs;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'long_distance_subscriptions' => $this->getLongDistanceSubscriptions(),
            'short_distance_subscriptions' => $this->getShortDistanceSubscriptions(),
            'nbPendingProofs' => $this->getNbPendingProofs(),
            'nbValidatedProofs' => $this->getNbValidatedProofs(),
            'nbRejectedProofs' => $this->getNbRejectedProofs(),
        ];
    }
}
