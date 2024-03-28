<?php

namespace App\Incentive\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Proposal;
use App\Incentive\Validator\CarpoolPaymentValidator;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A long distance journey.
 *
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__long_distance_journey")
 *
 * @ORM\HasLifecycleCallbacks
 */
class LongDistanceJourney extends Journey
{
    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"readSubscription"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Incentive\Entity\LongDistanceSubscription", inversedBy="longDistanceJourneys", cascade={"persist"})
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
     * @var null|CarpoolPayment
     *
     * @ORM\ManyToOne(targetEntity=CarpoolPayment::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolPayment;

    /**
     * The carpool item associate with the journey.
     *
     * @var null|CarpoolItem
     *
     * @ORM\ManyToOne(targetEntity=CarpoolItem::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolItem;

    /**
     * The proposal associate with the journey.
     *
     * @var null|Proposal
     *
     * @ORM\OneToOne(targetEntity=Proposal::class)
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $initialProposal;

    public function __construct(Proposal $proposal = null)
    {
        if (!is_null($proposal)) {
            $this->setInitialProposal($proposal);
        }
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->updatedAt = new \DateTime('now');
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
    public function getStartAddressLocality(): ?string
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
    public function getEndAddressLocality(): ?string
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
    public function getDistance(): ?int
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
    public function getCarpoolersNumber(): ?int
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
    public function getStartDate(): ?string
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
    public function getEndDate(): ?string
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
    public function getCarpoolPayment(): ?CarpoolPayment
    {
        return $this->carpoolPayment;
    }

    /**
     * Set the carpool proof associate with the journey.
     */
    public function setCarpoolPayment(?CarpoolPayment $carpoolPayment): self
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
     * Get the carpool item associate with the journey.
     */
    public function getCarpoolItem(): ?CarpoolItem
    {
        return $this->carpoolItem;
    }

    /**
     * Set the carpool item associate with the journey.
     *
     * @param null|CarpoolProof $carpoolItem the carpool item associate with the journey
     */
    public function setCarpoolItem(?CarpoolItem $carpoolItem): self
    {
        $this->carpoolItem = $carpoolItem;

        return $this;
    }

    public function getCarpoolProof(): ?CarpoolProof
    {
        return !is_null($this->getCarpoolItem()) ? $this->getCarpoolItem()->getCarpoolProof() : null;
    }

    /**
     * Get the proposal associate with the journey.
     */
    public function getInitialProposal(): ?Proposal
    {
        return $this->initialProposal;
    }

    /**
     * Set the proposal associate with the journey.
     *
     * @param null|Proposal $initialProposal the proposal associate with the journey
     */
    public function setInitialProposal(?Proposal $initialProposal): self
    {
        $this->initialProposal = $initialProposal;

        return $this;
    }

    public function getMatching(): ?Matching
    {
        if (is_null($this->getCarpoolItem())) {
            // Cas des trajets sans carpoolItems. Il faut passer par le proposal
            return null;                                                    // The journey was not carpooled
        }

        if (!is_null($this->getCarpoolItem())) {
            return $this->getCarpoolItem()->getAsk()->getMatching();        // The journey was carpooled
        }

        return null;                                                        // There was no connection for this trip
    }

    public function isCommitmentJourney(): ?bool
    {
        return
            !is_null($this->getSubscription())
            && !is_null($this->getSubscription()->getCommitmentProofJourney())
            && $this->getId() === $this->getSubscription()->getCommitmentProofJourney()->getId();
    }

    public function updateJourney(CarpoolItem $carpoolItem, CarpoolPayment $carpoolPayment, int $carpoolersNumber, array $addresses): self
    {
        $this->setCarpoolItem($carpoolItem);
        $this->setCarpoolPayment($carpoolPayment);
        $this->setStartAddressLocality($addresses['origin']);
        $this->setEndAddressLocality($addresses['destination']);
        $this->setDistance($this->carpoolItem->getAsk()->getMatching()->getCommonDistance());
        $this->setCarpoolersNumber($carpoolersNumber);
        $this->setStartDate($this->carpoolItem->getAsk()->getMatching()->getProposalOffer()->getCreatedDate()->format('Y-m-d H:i:s'));
        $this->setEndDate($carpoolPayment->getCreatedDate()->format('Y-m-d H:i:s'));

        return $this;
    }

    /**
     * Returns if the journey is EEC compliant
     * - The associated payment is successful and the transaction ID is not null
     * - The associated proof is type C, validated by the RPC.
     */
    public function isEECCompliant(): bool
    {
        return
            !is_null($this->getCarpoolPayment())
            && CarpoolPaymentValidator::isEecCompliant($this->getCarpoolPayment())
            && !is_null($this->getCarpoolProof())
            && CarpoolProofValidator::isEecCompliant($this->getCarpoolProof());
    }

    /**
     * Returns if the journey is EEC compliant only on display purpose.
     */
    public function isEECCompliantForDisplay(): bool
    {
        return
            !is_null($this->getCarpoolPayment())
            && $this->getCarpoolPayment()->isEECCompliantForDisplay();
    }
}
