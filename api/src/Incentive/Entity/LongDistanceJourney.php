<?php

namespace App\Incentive\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\Mapping as ORM;

/**
 * A long distance journey.
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__long_distance_journey")
 *
 * @ORM\HasLifecycleCallbacks
 */
class LongDistanceJourney
{
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
     * @ORM\ManyToOne(targetEntity="\App\Incentive\Entity\LongDistanceSubscription", inversedBy="longDistanceJourneys")
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
     * The carpool payment associate with the journey.
     *
     * @var CarpoolPayment
     *
     * @ORM\ManyToOne(targetEntity=CarpoolPayment::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolPayment;

    /**
     * The carpool proof associate with the journey.
     *
     * @var null|CarpoolProof
     *
     * @ORM\OneToOne(targetEntity=CarpoolProof::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolProof;

    /**
     * The proposal associate with the journey.
     *
     * @var null|CarpoolProof
     *
     * @ORM\OneToOne(targetEntity=Proposal::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $initialProposal;

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
     * Set date and time of the start of the operation.
     *
     * @param string $startDate Date and time of the start of the operation
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
    public function getSubscription(): LongDistanceSubscription
    {
        return $this->subscription;
    }

    /**
     * Set the value of subscription.
     */
    public function setSubscription(LongDistanceSubscription $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Get the carpool proof associate with the journey.
     */
    public function getCarpoolPayment(): CarpoolPayment
    {
        return $this->carpoolPayment;
    }

    /**
     * Set the carpool proof associate with the journey.
     *
     * @param CarpoolPayment $carpoolPayment the carpool proof associate with the journey
     */
    public function setCarpoolPayment(CarpoolPayment $carpoolPayment): self
    {
        $this->carpoolPayment = $carpoolPayment;

        return $this;
    }

    /**
     * Get status of http request to mobConnect.
     */
    public function getHttpRequestStatus(): int
    {
        return $this->httpRequestStatus;
    }

    /**
     * Set status of http request to mobConnect.
     *
     * @param mixed $httpRequestStatus
     */
    public function setHttpRequestStatus(int $httpRequestStatus): self
    {
        $this->httpRequestStatus = $httpRequestStatus;

        return $this;
    }

    /**
     * Get the carpool proof associate with the journey.
     */
    public function getCarpoolProof(): ?CarpoolProof
    {
        return $this->carpoolProof;
    }

    /**
     * Set the carpool proof associate with the journey.
     *
     * @param null|CarpoolProof $carpoolProof the carpool proof associate with the journey
     */
    public function setCarpoolProof($carpoolProof): self
    {
        $this->carpoolProof = $carpoolProof;

        return $this;
    }

    /**
     * Get the proposal associate with the journey.
     *
     * @return null|CarpoolProof
     */
    public function getInitialProposal(): ?Proposal
    {
        return $this->initialProposal;
    }

    /**
     * Set the proposal associate with the journey.
     *
     * @param null|CarpoolProof $initialProposal the proposal associate with the journey
     */
    public function setInitialProposal(?Proposal $initialProposal): self
    {
        $this->initialProposal = $initialProposal;

        return $this;
    }

    public function updateJourney(CarpoolProof $carpoolProof, CarpoolPayment $carpoolPayment, int $carpoolersNumber): self
    {
        $this->setCarpoolProof($carpoolProof);
        $this->setCarpoolPayment($carpoolPayment);
        $this->setStartAddressLocality($this->carpoolProof->getOriginDriverAddress()->getAddressLocality());
        $this->setEndAddressLocality($this->carpoolProof->getDestinationDriverAddress()->getAddressLocality());
        $this->setDistance($this->carpoolProof->getAsk()->getMatching()->getCommonDistance());
        $this->setCarpoolersNumber($carpoolersNumber);
        $this->setStartDate($this->carpoolProof->getAsk()->getMatching()->getProposalOffer()->getCreatedDate()->format('Y-m-d H:i:s'));
        $this->setEndDate($carpoolPayment->getCreatedDate()->format('Y-m-d H:i:s'));

        return $this;
    }
}
