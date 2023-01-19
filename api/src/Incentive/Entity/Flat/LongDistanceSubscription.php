<?php

namespace App\Incentive\Entity\Flat;

use App\Incentive\Entity\LongDistanceJourney;

class LongDistanceSubscription extends Subscription
{
    public function __construct(LongDistanceJourney $longDistanceJourney)
    {
        $this->subscription = $longDistanceJourney->getLongDistanceSubscription();

        $this->familyName = $this->subscription->getFamilyName();
        $this->givenName = $this->subscription->getGivenName();
        $this->drivingLicenceNumber = $this->subscription->getDrivingLicenceNumber();
        $this->email = $this->subscription->getEmail();
        $this->telephone = $this->subscription->getTelephone();
        $this->streetAddress = $this->subscription->getStreetAddress();
        $this->postalCode = $this->subscription->getPostalCode();
        $this->addressLocality = $this->subscription->getAddressLocality();
        $this->subscriptionId = $this->subscription->getSubscriptionId();

        $this->distance = $longDistanceJourney->getDistance();
        $this->carpoolerNumber = $longDistanceJourney->getCarpoolersNumber();
        $this->startAddressLocality = $longDistanceJourney->getStartAddressLocality();
        $this->endAddressLocality = $longDistanceJourney->getEndAddressLocality();
        $this->startDate = $longDistanceJourney->getStartDate();
        $this->endDate = $longDistanceJourney->getEndDate();
    }
}
