<?php

namespace App\Incentive\Entity\Subscription;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Entity\Subscription;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Undocumented class.
 */
class Progression
{
    /**
     * Indicates if the incentive registration has been finalized.
     *
     * @var null|bool
     *
     * @Groups({"readSubscription"})
     */
    private $registrationFinalized;

    /**
     * Indicates if the 1st LD carpool has been published.
     *
     * @var bool
     *
     * @Groups({"readSubscription"})
     */
    private $firstCarpoolPublished;

    /**
     * Indicates if the 1st carpooling is observed.
     *
     * @var null|bool
     *
     * @Groups({"readSubscription"})
     */
    private $carpoolRegistered;

    /**
     * Indicates if the SD commitment carpoolProof has been validated by the RPC.
     *
     * @var null|bool
     *
     * @Groups({"readSubscription"})
     */
    private $carpoolValidated;

    /**
     * Indicates if the LD commitment carpoolProof has been payed and validated by the RPC.
     *
     * @var null|bool
     *
     * @Groups({"readSubscription"})
     */
    private $carpoolPayedAndValidated;

    /**
     * Indicates if the subscription has been bonified.
     *
     * @var null|bool
     *
     * @Groups({"readSubscription"})
     */
    private $subscriptionBonified;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_subscription;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct($subscription)
    {
        $this->_subscription = $subscription;
    }

    /**
     * Get indicates if the incentive registration has been finalized.
     */
    public function isRegistrationFinalized(): ?bool
    {
        return $this->_subscription->hasIncentiveToken();
    }

    /**
     * Get indicates if the 1st carpool has been published.
     */
    public function isFirstCarpoolPublished(): ?bool
    {
        return $this->isRegistrationFinalized() && $this->_subscription instanceof LongDistanceSubscription
            ? $this->_subscription->isFirstCarpoolPublished() : null;
    }

    /**
     * Get indicates if the 1st carpooling is observed.
     */
    public function isCarpoolRegistered(): ?bool
    {
        return (
            $this->_subscription instanceof LongDistanceSubscription
            && $this->isFirstCarpoolPublished()
        ) || (
            $this->_subscription instanceof ShortDistanceSubscription
            && $this->isRegistrationFinalized()
            && !is_null($this->_subscription->getCarpoolRegistered())
        )
            ? $this->_subscription->getCarpoolRegistered() && $this->_subscription->hasCommitToken() : null;
    }

    /**
     * Get indicates if the SD commitment carpoolProof has been validated by the RPC.
     */
    public function isCarpoolValidated(): ?bool
    {
        return $this->_subscription instanceof ShortDistanceSubscription && $this->isCarpoolRegistered()
            ? $this->_subscription->isCommitmentJourneyValidated() && $this->_subscription->hasHonorCertificateToken() : null;
    }

    /**
     * Get indicates if the LD commitment carpoolProof has been payed and validated by the RPC.
     */
    public function isCarpoolPayedAndValidated(): ?bool
    {
        return $this->_subscription instanceof LongDistanceSubscription && $this->isCarpoolRegistered()
            ? $this->_subscription->isCommitmentJourneyPayedAndValidated() && $this->_subscription->hasHonorCertificateToken() : null;
    }

    /**
     * Get indicates if the subscription has been bonified.
     */
    public function isSubscriptionBonified(): ?bool
    {
        if ((
            $this->_subscription instanceof LongDistanceSubscription
            && $this->isCarpoolPayedAndValidated()
        ) || (
            $this->_subscription instanceof LongDistanceSubscription
            && $this->isCarpoolValidated()
        )) {
            switch ($this->_subscription->getBonusStatus()) {
                case Subscription::BONUS_STATUS_PENDING: return null;

                case Subscription::BONUS_STATUS_NO: return false;

                case Subscription::BONUS_STATUS_OK: return true;

                default: return null;
            }
        }

        return null;
    }
}
