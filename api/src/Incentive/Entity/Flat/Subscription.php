<?php

namespace App\Incentive\Entity\Flat;

use Symfony\Component\Serializer\Annotation\Groups;

abstract class Subscription
{
    protected $subscription;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $addressLocality;

    /**
     * @var int
     *
     * @Groups({"readSubscription"})
     */
    protected $carpoolerNumber;

    /**
     * @var int
     *
     * @Groups({"readSubscription"})
     */
    protected $distance;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $drivingLicenceNumber;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $email;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $endAddressLocality;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $endDate;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $familyName;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $finalTimestamp;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $givenName;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $initialTimeStamp;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $postalCode;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $status;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $startAddressLocality;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $startDate;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $streetAddress;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $subscriptionId;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    protected $telephone;

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function getCarpoolerNumber(): ?int
    {
        return $this->carpoolerNumber;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function getDrivingLicenceNumber(): ?string
    {
        return $this->drivingLicenceNumber;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getEndAddressLocality(): ?string
    {
        return $this->endAddressLocality;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getFinalTimestamp(): ?string
    {
        return $this->finalTimestamp;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getInitialTimeStamp(): ?string
    {
        return $this->initialTimeStamp;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getStartAddressLocality(): ?string
    {
        return $this->startAddressLocality;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function getSubscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }
}
