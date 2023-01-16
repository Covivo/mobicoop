<?php

namespace App\Incentive\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\Payment\Entity\CarpoolPayment;
use Doctrine\ORM\Mapping as ORM;

/**
 * A long distance journey.
 *
 * @ORM\Entity
 * @ORM\Table(name="mobconnect__long_distance_journey")
 * @ORM\HasLifecycleCallbacks
 */
class LongDistanceJourney
{
    public const STANDARDIZED_SHEET_OPERATION = 'TRA-SE-115';

    public const BONUS_STATUS_PENDING = 0;
    public const BONUS_STATUS_NO = 1;
    public const BONUS_STATUS_OK = 2;

    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="\App\Incentive\Entity\LongDistanceSubscription", inversedBy="longDistanceJourneys")
     */
    private $longDistanceSubscription;

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
     * @var int the distance in meter of the journey
     *
     * @ORM\Column(type="integer", nullable=true)
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
     * Bonus Status of the journey.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 1, "comment":"Bonus Status of the EEC form"})
     */
    private $bonusStatus = self::BONUS_STATUS_NO;

    /**
     * The carpool proof associate with the journey.
     *
     * @var CarpoolPayment
     *
     * @ORM\OneToOne(targetEntity=CarpoolPayment::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $carpoolPayment;

    public function __construct(CarpoolPayment $carpoolPayment, CarpoolProof $carpoolProof, int $carpoolersNumber)
    {
        $this->setCarpoolPayment($carpoolPayment);
        $this->setStartAddressLocality($carpoolProof->getOriginDriverAddress()->getAddressLocality());
        $this->setEndAddressLocality($carpoolProof->getDestinationDriverAddress()->getAddressLocality());
        $this->setDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance());
        $this->setCarpoolersNumber($carpoolersNumber);

        $date = $carpoolProof->getAsk()->getMatching()->getCriteria()->getFromDate()->format('Y-m-d');
        $time = !is_null($carpoolProof->getAsk()->getMatching()->getCriteria()->getFromTime())
            ? $carpoolProof->getAsk()->getMatching()->getCriteria()->getFromTime()->format('H:i:s') : '00:00:00';

        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', "{$date} {$time}");
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('PT'.$carpoolProof->getAsk()->getMatching()->getNewDuration().'S'));
        $this->setStartDate($startDate->format('Y-m-d H:i:s'));
        $this->setEndDate($endDate->format('Y-m-d H:i:s'));
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
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
        $this->distance = $distance;

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
     * Get the value of longDistanceSubscription.
     */
    public function getlongDistanceSubscription(): LongDistanceSubscription
    {
        return $this->longDistanceSubscription;
    }

    /**
     * Set the value of longDistanceSubscription.
     */
    public function setLongDistanceSubscription(LongDistanceSubscription $longDistanceSubscription): self
    {
        $this->longDistanceSubscription = $longDistanceSubscription;

        return $this;
    }

    /**
     * Get bonus Status of the EEC form.
     */
    public function getBonusStatus(): int
    {
        return $this->bonusStatus;
    }

    /**
     * Set bonus Status of the EEC form.
     *
     * @param int $bonusStatus bonus Status of the EEC form
     */
    public function setBonusStatus(int $bonusStatus): self
    {
        $this->bonusStatus = $bonusStatus;

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
}
