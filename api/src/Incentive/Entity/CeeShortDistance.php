<?php

namespace App\Incentive\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A short distance cee.
 *
 * @ORM\Entity
 * @ORM\Table(name="cee__short_distance")
 * @ORM\HasLifecycleCallbacks
 */
class CeeShortDistance
{
    public const STANDARDIZED_SHEET_OPERATION = 'TRA-SE-115';

    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string the first name of the user
     *
     * @ORM\Column(type="string", length=255)
     */
    private $givenName;

    /**
     * @var string the family name of the user
     *
     * @ORM\Column(type="string", length=255)
     */
    private $familyName;

    /**
     * @var string the driving licence number of the user
     *
     * @ORM\Column(type="string", length=15)
     */
    private $drivingLicenseNumber;

    /**
     * @var string the full street address of the user
     *
     * @ORM\Column(type="string", length=255)
     */
    private $streetAddress;

    /**
     * @var string the address postal code of the user
     *
     * @ORM\Column(type="string", length=15)
     */
    private $postalCode;

    /**
     * @var string the address locality of the user
     *
     * @ORM\Column(type="string", length=100)
     */
    private $addressLocality;

    /**
     * @var string the start locality of the journey
     *
     * @ORM\Column(type="string", length=100)
     */
    private $startAddressLocality;

    /**
     * @var string the end locality of the journey
     *
     * @ORM\Column(type="string", length=100)
     */
    private $endAddressLocality;

    /**
     * @var string the reference of the CEE sheet operation
     *
     * @ORM\Column(type="string", length=15)
     */
    private $standardizedOperationRef = self::STANDARDIZED_SHEET_OPERATION;

    /**
     * @var int the distance in meter of the journey
     *
     * @ORM\Column(type="integer")
     */
    private $distance;

    /**
     * @var int the carpoolers number of the journey
     *
     * @ORM\Column(type="integer")
     */
    private $carpoolersNumber;

    /**
     * @var string the ID of the user
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $operatorUserId;

    /**
     * @var string the ID of the RPC journey
     *
     * @ORM\Column(type="string", unique=true)
     */
    private $rpcJourneyId;

    /**
     * @var string the status of the user
     *
     * @ORM\Column(type="string")
     */
    private $rpcNumberStatus;

    /**
     * @var \DateTimeInterface Date and time of the start of the operation
     *
     * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface Date and time of the end of the operation
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @var string the telephone number of the user
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $telephone;

    /**
     * @var string the email of the user
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * Get l'ID de la page.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the first name of the user.
     */
    public function getGivenName(): string
    {
        return $this->givenName;
    }

    /**
     * Set the first name of the user.
     */
    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Get the family name of the user.
     */
    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    /**
     * Set the family name of the user.
     */
    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Get the driving licence number of the user.
     */
    public function getDrivingLicenseNumber(): string
    {
        return $this->drivingLicenseNumber;
    }

    /**
     * Set the driving licence number of the user.
     */
    public function setDrivingLicenseNumber(string $drivingLicenseNumber): self
    {
        $this->drivingLicenseNumber = $drivingLicenseNumber;

        return $this;
    }

    /**
     * Get the full street address of the user.
     */
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    /**
     * Set the full street address of the user.
     */
    public function setStreetAddress(string $streetAddress): self
    {
        $this->streetAddress = $streetAddress;

        return $this;
    }

    /**
     * Get the address postal code of the user.
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * Set the address postal code of the user.
     *
     * @param string $postalCode the address postal code of the user
     */
    public function setPostalCode(string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Get the address locality of the user.
     */
    public function getAddressLocality(): string
    {
        return $this->addressLocality;
    }

    /**
     * Set the address locality of the user.
     *
     * @param string $addressLocality the address locality of the user
     */
    public function setAddressLocality(string $addressLocality): self
    {
        $this->addressLocality = $addressLocality;

        return $this;
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
    public function setRpcNumberStatus(string $rpcNumberStatus): self
    {
        $this->rpcNumberStatus = $rpcNumberStatus;

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
    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    /**
     * Set date and time of the end of the operation.
     */
    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get date and time of the end of the operation.
     */
    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    /**
     * Set date and time of the end of the operation.
     */
    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get the reference of the CEE sheet operation.
     */
    public function getStandardizedOperationRef(): string
    {
        return $this->standardizedOperationRef;
    }

    /**
     * Get the telephone number of the user.
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Set the telephone number of the user.
     */
    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get the email of the user.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the email of the user.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
