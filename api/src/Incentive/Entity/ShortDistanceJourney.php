<?php

namespace App\Incentive\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * A short distance journey.
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__short_distance_journey")
 *
 * @ORM\HasLifecycleCallbacks
 */
class ShortDistanceJourney
{
    public const RPC_NUMBER_STATUS = 'OK';

    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Incentive\Entity\ShortDistanceSubscription", inversedBy="shortDistanceSubscriptions")
     */
    private $subscription;

    /**
     * @var string the start locality of the journey
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $startAddressLocality;

    /**
     * @var string the end locality of the journey
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $endAddressLocality;

    /**
     * @var int the distance in kilometer of the journey
     *
     * @ORM\Column(type="decimal", scale=1, precision=5, nullable=true)
     */
    private $distance;

    /**
     * @var int the carpoolers number of the journey
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $carpoolersNumber;

    /**
     * @var string the ID of the user
     *
     * @ORM\Column(type="string", unique=false, nullable=true)
     */
    private $operatorUserId;

    /**
     * @var string the ID of the RPC journey
     *
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $rpcJourneyId;

    /**
     * @var string the status of the user
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $rpcNumberStatus;

    /**
     * @var string Date and time of the start of the operation
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $startDate;

    /**
     * @var string Date and time of the end of the operation
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $endDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    /**
     * Status of http request to mobConnect.
     *
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true, options={"comment":"Status of http request to mobConnect"})
     */
    private $httpRequestStatus;

    /**
     * The carpool proof associate with the journey.
     *
     * @var CarpoolProof
     *
     * @ORM\OneToOne(targetEntity=CarpoolProof::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolProof;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get l'ID de la page.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the start locality of the journey.
     */
    public function getStartAddressLocality(): string
    {
        return $this->startAddressLocality;
    }

    /**
     * Set the start locality of the journey.
     *
     * @param string $startAddressLocality the start locality of the journey
     */
    public function setStartAddressLocality(string $startAddressLocality): self
    {
        $this->startAddressLocality = $startAddressLocality;

        return $this;
    }

    /**
     * Get the end locality of the journey.
     */
    public function getEndAddressLocality(): string
    {
        return $this->endAddressLocality;
    }

    /**
     * Set the end locality of the journey.
     *
     * @param string $endAddressLocality the end locality of the journey
     */
    public function setEndAddressLocality(string $endAddressLocality): self
    {
        $this->endAddressLocality = $endAddressLocality;

        return $this;
    }

    /**
     * Get the distance in meter of the journey.
     */
    public function getDistance(): int
    {
        return $this->distance;
    }

    /**
     * Set the distance in meter of the journey.
     */
    public function setDistance(int $distance): self
    {
        // We convert the distance given in meter to kilometer
        $this->distance = $distance / 1000;

        return $this;
    }

    /**
     * Get the ID of the user.
     */
    public function getOperatorUserId(): string
    {
        return $this->operatorUserId;
    }

    /**
     * Set the ID of the user.
     */
    public function setOperatorUserId(int $operatorUserId): self
    {
        $this->operatorUserId = $operatorUserId;

        return $this;
    }

    /**
     * Get the value of rpcJourneyId.
     */
    public function getRpcJourneyId(): string
    {
        return $this->rpcJourneyId;
    }

    /**
     * Set the value of rpcJourneyId.
     *
     * @param mixed $rpcJourneyId
     */
    public function setRpcJourneyId(string $rpcJourneyId): self
    {
        $this->rpcJourneyId = $rpcJourneyId;

        return $this;
    }

    /**
     * Get the status of the user.
     */
    public function getRpcNumberStatus(): string
    {
        return $this->rpcNumberStatus;
    }

    /**
     * Set the status of the user.
     *
     * @param string $rpcNumberStatus the status of the user
     */
    public function setRpcNumberStatus(): self
    {
        $this->rpcNumberStatus = self::RPC_NUMBER_STATUS;

        return $this;
    }

    /**
     * Get the carpoolers number of the journey.
     */
    public function getCarpoolersNumber(): int
    {
        return $this->carpoolersNumber;
    }

    /**
     * Set the carpoolers number of the journey.
     */
    public function setCarpoolersNumber(int $carpoolersNumber): self
    {
        $this->carpoolersNumber = $carpoolersNumber;

        return $this;
    }

    /**
     * Get date and time of the start of the operation.
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * Set date and time of the end of the operation.
     */
    public function setStartDate(string $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get date and time of the end of the operation.
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * Set date and time of the end of the operation.
     */
    public function setEndDate(string $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the value of createdAt.
     *
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get the value of updatedAt.
     *
     * @return \DateTimeInterface
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get the value of subscription.
     */
    public function getSubscription(): ShortDistanceSubscription
    {
        return $this->subscription;
    }

    /**
     * Set the value of subscription.
     */
    public function setSubscription(ShortDistanceSubscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get the carpool proof associate with the journey.
     */
    public function getCarpoolProof(): CarpoolProof
    {
        return $this->carpoolProof;
    }

    /**
     * Set the carpool proof associate with the journey.
     *
     * @param CarpoolProof $carpoolProof the carpool proof associate with the journey
     */
    public function setCarpoolProof(CarpoolProof $carpoolProof): self
    {
        $this->carpoolProof = $carpoolProof;

        return $this;
    }

    /**
     * Get status of http request to mobConnect.
     */
    public function getHttpRequestStatus(): ?int
    {
        return $this->httpRequestStatus;
    }

    /**
     * Set status of http request to mobConnect.
     */
    public function setHttpRequestStatus(int $httpRequestStatus): self
    {
        $this->httpRequestStatus = $httpRequestStatus;

        return $this;
    }

    public function updateJourney(CarpoolProof $carpoolProof, string $rpcJourneyId, int $carpoolersNumber)
    {
        $this->setCarpoolProof($carpoolProof);
        $this->setStartAddressLocality($carpoolProof->getOriginDriverAddress()->getAddressLocality());
        $this->setEndAddressLocality($carpoolProof->getDestinationDriverAddress()->getAddressLocality());
        $this->setDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance());
        $this->setOperatorUserId($carpoolProof->getDriver()->getId());
        $this->setStartDate($carpoolProof->getAsk()->getMatching()->getProposalOffer()->getCreatedDate()->format('Y-m-d H:i:s'));
        $this->setEndDate($carpoolProof->getCreatedDate()->format('Y-m-d H:i:s'));
        $this->setRpcJourneyId($rpcJourneyId);
        $this->setRpcNumberStatus();
        $this->setCarpoolersNumber($carpoolersNumber);
    }
}
