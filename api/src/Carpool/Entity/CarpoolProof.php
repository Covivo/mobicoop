<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

namespace App\Carpool\Entity;

use App\Geography\Entity\Address;
use App\Geography\Entity\Direction;
use App\Incentive\Entity\ShortDistanceJourney;
use App\Payment\Entity\CarpoolItem;
use App\Payment\Entity\CarpoolPayment;
use App\User\Entity\User;
use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : carpool proof.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class CarpoolProof
{
    public const STATUS_INITIATED = 0;              // not ready to be sent, proof still under construction
    public const STATUS_PENDING = 1;                // ready to be sent
    public const STATUS_SENT = 2;                   // sent
    public const STATUS_ERROR = 3;                  // error during the sending
    public const STATUS_CANCELED = 4;               // cancellation before sending
    public const STATUS_ACQUISITION_ERROR = 5;      // proof not recorded by the carpool register
    public const STATUS_NORMALIZATION_ERROR = 6;    // proof recorded but data not normalized by the carpool register
    public const STATUS_FRAUD_ERROR = 7;            // fraud detected by carpool register
    public const STATUS_VALIDATED = 8;              // proof validated by the carpool register
    public const STATUS_EXPIRED = 9;                // proof sent too late to the carpool register
    public const STATUS_CANCELED_BY_OPERATOR = 10;  // proof canceled by the operator
    public const STATUS_UNDER_CHECKING = 11;        // proof under review by the carpool register
    public const STATUS_UNKNOWN = 12;               // status unknown by the RPC (proof exists but... unknown)
    public const STATUS_INVALID_CONCURRENT_SCHEDULES = 13; // proof not sent: concurrent travel at the same time already sent to rpc
    public const STATUS_INVALID_SPLITTED_TRIP = 14; // proof not sent: a long trip has been splitted

    public const ACTOR_DRIVER = 1;
    public const ACTOR_PASSENGER = 2;

    public const TYPE_LOW = 'A';
    public const TYPE_MID = 'B';
    public const TYPE_HIGH = 'C';

    public const TYPE_UNDETERMINED_CLASSIC = 'CX';
    public const TYPE_UNDETERMINED_DYNAMIC = 'DX';

    public const MINIMUM_DISTANCE_GPS_FOR_TYPE_HIGH = 3000; // Minimum distance required between driver/passenger pickUp/dropOff in meters

    public const ERROR_STATUS = [
        self::STATUS_ERROR,
        self::STATUS_CANCELED,
        self::STATUS_ACQUISITION_ERROR,
        self::STATUS_NORMALIZATION_ERROR,
        self::STATUS_FRAUD_ERROR,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELED_BY_OPERATOR,
    ];

    /**
     * @var int the id of this proof
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"readSubscription"})
     */
    private $id;

    /**
     * @var int proof status (0 = pending, 1 = sent to the register; 2 = error while sending to the register)
     *
     * @Assert\NotBlank
     *
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @var string register system proof type
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $type;

    /**
     * @var Ask the ask related to the proof
     *
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Ask", inversedBy="carpoolProofs")
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $ask;

    /**
     * @var \DateTimeInterface driver start date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startDriverDate;

    /**
     * @var \DateTimeInterface driver end date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDriverDate;

    /**
     * @var Address origin of the driver
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originDriverAddress;

    /**
     * @var Address destination of the driver
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $destinationDriverAddress;

    /**
     * @var \DateTimeInterface passenger pickup certification date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pickUpPassengerDate;

    /**
     * @var \DateTimeInterface driver pickup certification date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pickUpDriverDate;

    /**
     * @var \DateTimeInterface passenger dropoff certification date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dropOffPassengerDate;

    /**
     * @var \DateTimeInterface driver dropoff certification date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dropOffDriverDate;

    /**
     * @var Address position of the passenger when pickup certification is asked
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickUpPassengerAddress;

    /**
     * @var Address position of the driver when pickup certification is asked
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $pickUpDriverAddress;

    /**
     * @var Address position of the passenger when dropoff certification is asked
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropOffPassengerAddress;

    /**
     * @var Address position of the driver when dropoff certification is asked
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $dropOffDriverAddress;

    /**
     * @var null|Direction the direction related with the proof
     *
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Direction", cascade={"persist"})
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $direction;

    /**
     * @var string History of geographic points as a linestring, used to compute the direction. Updated at each new position. Can be emptied when the carpool is finished.
     *
     * @ORM\Column(type="linestring", nullable=true)
     */
    private $geoJsonPoints;

    /**
     * @var null|User the driver, used to keep a link to the driver if the passenger deletes its ad (the ask may be deleted aswell)
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="carpoolProofsAsDriver")
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $driver;

    /**
     * @var null|User the passenger, used to keep a link to the passenger if the driver deletes its ad (the ask may be deleted aswell)
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="carpoolProofsAsPassenger")
     *
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $passenger;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var null|array The array of points as Address objects. Used to create the geoJsonPoints.
     */
    private $points;

    /**
     * @ORM\OneToOne(targetEntity=ShortDistanceJourney::class, mappedBy="carpoolProof")
     */
    private $mobConnectShortDistanceJourney;

    /**
     * @var \DateTimeInterface validated date by RPC (status = ok)
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $validatedDate;

    /**
     * @var null|string driver's phone unique id
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $driverPhoneUniqueId;

    /**
     * @var null|string passenger's phone unique id
     *
     *  @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passengerPhoneUniqueId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        if (self::STATUS_VALIDATED == $status && is_null($this->getValidatedDate())) {
            $this->setValidatedDate(new \DateTime('now'));
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAsk(): ?Ask
    {
        return $this->ask;
    }

    public function setAsk(?Ask $ask): self
    {
        $this->ask = $ask;
        if (!is_null($ask)) {
            // set the owning side
            $ask->addCarpoolProof($this);
        }

        return $this;
    }

    public function getPickUpPassengerDate(): ?\DateTimeInterface
    {
        return $this->pickUpPassengerDate;
    }

    public function setPickUpPassengerDate(\DateTimeInterface $pickUpPassengerDate): self
    {
        $this->pickUpPassengerDate = $pickUpPassengerDate;

        return $this;
    }

    public function getPickUpDriverDate(): ?\DateTimeInterface
    {
        return $this->pickUpDriverDate;
    }

    public function setPickUpDriverDate(\DateTimeInterface $pickUpDriverDate): self
    {
        $this->pickUpDriverDate = $pickUpDriverDate;

        return $this;
    }

    public function getDropOffPassengerDate(): ?\DateTimeInterface
    {
        return $this->dropOffPassengerDate;
    }

    public function setDropOffPassengerDate(\DateTimeInterface $dropOffPassengerDate): self
    {
        $this->dropOffPassengerDate = $dropOffPassengerDate;

        return $this;
    }

    public function getDropOffDriverDate(): ?\DateTimeInterface
    {
        return $this->dropOffDriverDate;
    }

    public function setDropOffDriverDate(\DateTimeInterface $dropOffDriverDate): self
    {
        $this->dropOffDriverDate = $dropOffDriverDate;

        return $this;
    }

    public function getPickUpPassengerAddress(): ?Address
    {
        return $this->pickUpPassengerAddress;
    }

    public function setPickUpPassengerAddress(?Address $pickUpPassengerAddress): self
    {
        $this->pickUpPassengerAddress = $pickUpPassengerAddress;

        return $this;
    }

    public function getPickUpDriverAddress(): ?Address
    {
        return $this->pickUpDriverAddress;
    }

    public function setPickUpDriverAddress(?Address $pickUpDriverAddress): self
    {
        $this->pickUpDriverAddress = $pickUpDriverAddress;

        return $this;
    }

    public function getDropOffPassengerAddress(): ?Address
    {
        return $this->dropOffPassengerAddress;
    }

    public function setDropOffPassengerAddress(?Address $dropOffPassengerAddress): self
    {
        $this->dropOffPassengerAddress = $dropOffPassengerAddress;

        return $this;
    }

    public function getDropOffDriverAddress(): ?Address
    {
        return $this->dropOffDriverAddress;
    }

    public function setDropOffDriverAddress(?Address $dropOffDriverAddress): self
    {
        $this->dropOffDriverAddress = $dropOffDriverAddress;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getGeoJsonPoints()
    {
        return $this->geoJsonPoints;
    }

    public function setGeoJsonPoints($geoJsonPoints): self
    {
        $this->geoJsonPoints = $geoJsonPoints;

        return $this;
    }

    public function getDriver(): ?User
    {
        return $this->driver;
    }

    public function setDriver(?User $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getPassenger(): ?User
    {
        return $this->passenger;
    }

    public function setPassenger(?User $passenger): self
    {
        $this->passenger = $passenger;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getPoints(): ?array
    {
        return $this->points;
    }

    public function setPoints(array $points): self
    {
        $this->points = $points;

        return $this;
    }

    public function getStartDriverDate(): ?\DateTimeInterface
    {
        return $this->startDriverDate;
    }

    public function setStartDriverDate(\DateTimeInterface $startDriverDate): self
    {
        $this->startDriverDate = $startDriverDate;

        return $this;
    }

    public function getEndDriverDate(): ?\DateTimeInterface
    {
        return $this->endDriverDate;
    }

    public function setEndDriverDate(\DateTimeInterface $endDriverDate): self
    {
        $this->endDriverDate = $endDriverDate;

        return $this;
    }

    public function getOriginDriverAddress(): ?Address
    {
        return $this->originDriverAddress;
    }

    public function setOriginDriverAddress(?Address $originDriverAddress): self
    {
        $this->originDriverAddress = $originDriverAddress;

        return $this;
    }

    public function getDestinationDriverAddress(): ?Address
    {
        return $this->destinationDriverAddress;
    }

    public function setDestinationDriverAddress(?Address $destinationDriverAddress): self
    {
        $this->destinationDriverAddress = $destinationDriverAddress;

        return $this;
    }

    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validatedDate;
    }

    public function setValidatedDate(\DateTimeInterface $validatedDate): self
    {
        $this->validatedDate = $validatedDate;

        return $this;
    }

    public function getDriverPhoneUniqueId(): ?string
    {
        return $this->driverPhoneUniqueId;
    }

    public function setDriverPhoneUniqueId(?string $driverPhoneUniqueId): self
    {
        $this->driverPhoneUniqueId = $driverPhoneUniqueId;

        return $this;
    }

    public function getPassengerPhoneUniqueId(): ?string
    {
        return $this->passengerPhoneUniqueId;
    }

    public function setPassengerPhoneUniqueId(?string $passengerPhoneUniqueId): self
    {
        $this->passengerPhoneUniqueId = $passengerPhoneUniqueId;

        return $this;
    }

    // DOCTRINE EVENTS

    /**
     * Status.
     *
     * @ORM\PrePersist
     */
    public function setAutoStatus()
    {
        if (is_null($this->getStatus())) {
            $this->setStatus(self::STATUS_INITIATED);
        }
    }

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }

    /**
     * GeoJson representation of the points.
     *
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function setAutoGeoJsonPoints()
    {
        if (!is_null($this->getPoints())) {
            $arrayPoints = [];
            foreach ($this->getPoints() as $address) {
                $arrayPoints[] = new Point($address->getLongitude(), $address->getLatitude());
            }
            $this->setGeoJsonPoints(new LineString($arrayPoints));
        }
    }

    /**
     * Get the value of mobConnectShortDistanceJourney.
     */
    public function getMobConnectShortDistanceJourney(): ?ShortDistanceJourney
    {
        return $this->mobConnectShortDistanceJourney;
    }

    /**
     * Set the value of mobConnectShortDistanceJourney.
     */
    public function setMobConnectShortDistanceJourney(ShortDistanceJourney $mobConnectShortDistanceJourney): self
    {
        $this->mobConnectShortDistanceJourney = $mobConnectShortDistanceJourney;

        return $this;
    }

    public function getCarpoolItem(): ?CarpoolItem
    {
        if (
            is_null($this->getAsk())
            || !is_null($this->getAsk()) && empty($this->getAsk()->getCarpoolItems())
        ) {
            return null;
        }

        $carpoolProofId = $this->getId();

        $filteredCarpoolItems = array_values(array_filter($this->getAsk()->getCarpoolItems(), function (CarpoolItem $carpoolItem) use ($carpoolProofId) {
            if (is_null($carpoolItem->getCarpoolProof())) {
                return null;
            }

            return $carpoolProofId === $carpoolItem->getCarpoolProof()->getId();
        }));

        return !empty($filteredCarpoolItems) ? $filteredCarpoolItems[0] : null;
    }

    /**
     * Used in the context of CEE, checks and returns if proof is awaiting validation of the RPC.
     */
    public function isStatusPending(): bool
    {
        $status = $this->getStatus();

        return
            self::STATUS_INITIATED === $status
            || self::STATUS_PENDING === $status
            || self::STATUS_SENT === $status
            || self::STATUS_UNDER_CHECKING === $status;
    }

    /**
     * Used in the context of CEE, return the associated payment. This latest must meet the criteria:
     * - Have been successfully paid,
     * - Keep track of the transaction.
     */
    public function getSuccessfullPayment(): ?CarpoolPayment
    {
        return !is_null($this->getCarpoolItem()) ? $this->getCarpoolItem()->getSuccessfullPayment() : null;
    }

    /**
     * Used in the context of CEE, returns the matching common distance.
     */
    public function getDistance(): ?int
    {
        return !is_null($this->getAsk())
            && !is_null($this->getAsk()->getMatching())
            ? $this->getAsk()->getMatching()->getCommonDistance()
            : null;
    }

    public function isGeolocatedAddressesPresent(): bool
    {
        return
            !is_null($this->getPickUpDriverAddress())
            && !is_null($this->getPickUpPassengerAddress())
            && !is_null($this->getDropOffDriverAddress())
            && !is_null($this->getDropOffPassengerAddress());
    }
}
