<?php

namespace App\Incentive\Entity\Flat;

use App\Incentive\Entity\ShortDistanceJourney;
use Symfony\Component\Serializer\Annotation\Groups;

class ShortDistanceSubscription extends Subscription
{
    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    private $operatorUserId;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    private $rpcJourneyId;

    /**
     * @var string
     *
     * @Groups({"readSubscription"})
     */
    private $rpcNumberStatus;

    public function __construct(ShortDistanceJourney $shortDistanceJourney)
    {
        $this->subscription = $shortDistanceJourney->getShortDistanceSubscription();

        $this->familyName = $this->subscription->getFamilyName();
        $this->givenName = $this->subscription->getGivenName();
        $this->drivingLicenceNumber = $this->subscription->getDrivingLicenceNumber();
        $this->email = $this->subscription->getEmail();
        $this->telephone = $this->subscription->getTelephone();
        $this->streetAddress = $this->subscription->getStreetAddress();
        $this->postalCode = $this->subscription->getPostalCode();
        $this->addressLocality = $this->subscription->getAddressLocality();
        $this->subscriptionId = $this->subscription->getSubscriptionId();

        $this->distance = $shortDistanceJourney->getDistance();
        $this->carpoolerNumber = $shortDistanceJourney->getCarpoolersNumber();
        $this->startAddressLocality = $shortDistanceJourney->getStartAddressLocality();
        $this->endAddressLocality = $shortDistanceJourney->getEndAddressLocality();
        $this->startDate = $shortDistanceJourney->getStartDate();
        $this->endDate = $shortDistanceJourney->getEndDate();

        $this->operatorUserId = $shortDistanceJourney->getOperatorUserId();
        $this->rpcJourneyId = $shortDistanceJourney->getRpcJourneyId();
        $this->rpcNumberStatus = $shortDistanceJourney->getRpcNumberStatus();
    }

    public function getOperatorUserId(): ?string
    {
        return $this->operatorUserId;
    }

    public function getRpcJourneyId(): ?string
    {
        return $this->rpcJourneyId;
    }

    public function getRpcNumberStatus(): ?string
    {
        return $this->rpcNumberStatus;
    }
}
