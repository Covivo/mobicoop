<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\EecResponse;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Manager\MobConnectManager;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserValidation extends Validation
{
    public function __construct(LoggerService $loggerService, TokenStorageInterface $tokenStorageInterface)
    {
        $this->_tokenStorage = $tokenStorageInterface;

        parent::__construct($loggerService);
    }

    public function isUserProperlyConnectToMob(User $driver): bool
    {
        $this->setDriver($driver);

        return
            !is_null($this->_driver->getMobConnectAuth())
            && $this->_driver->getMobConnectAuth()->getRefreshTokenExpiresDate() > new \DateTime('now');
    }

    public function isUserValidForEEC(User $user, string $subscriptionType = MobConnectManager::LONG_SUBSCRIPTION_TYPE): array
    {
        $errors = [];

        if (!$this->hasValidMobConnectAuth($user)) {
            array_push($errors, EecResponse::ERROR_INVALID_AUTH);
        }

        if (!$this->_hasSubscribedTo($user, $subscriptionType)) {
            array_push($errors, str_replace('[TYPE]', $subscriptionType, EecResponse::ERROR_SUBSCRIPTION_MISSING));
        }

        return $errors;
    }

    /**
     * Returns if a user has subscribed to a subscription.
     */
    private function _hasSubscribedTo(User $user, string $subscriptionType = MobConnectManager::LONG_SUBSCRIPTION_TYPE): bool
    {
        $getter = 'get'.ucfirst($subscriptionType).'DistanceSubscription';

        return !is_null($user->{$getter}());
    }

    private function _getCarpoolProofsForLongDistance(array $carpoolProofs): array
    {
        return array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) {
            return
                !is_null($carpoolProof->getAsk())
                && !is_null($carpoolProof->getAsk()->getMatching())
                && $this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())   // The trip must have a distance greater than or equal to 80km
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()                                         // The trip must have a carpool class C
                && $this->isOriginOrDestinationFromFrance($carpoolProof)                                        // The trip must depart or arrive from the reference country
                && !$this->isDateInPeriod($carpoolProof->getStartDriverDate());                                  // User must not have traveled long distance for a period of 3 months
        });
    }

    private function _getCarpoolProofsForShortDistance(array $carpoolProofs): array
    {
        return array_filter($carpoolProofs, function (CarpoolProof $carpoolProof) {
            return
                !is_null($carpoolProof->getAsk())
                && !is_null($carpoolProof->getAsk()->getMatching())
                && !$this->isDistanceLongDistance($carpoolProof->getAsk()->getMatching()->getCommonDistance())      // The trip must have a distance of less than 80km
                && CarpoolProof::TYPE_HIGH === $carpoolProof->getType()                                             // The trip must have a carpool class C
                && $this->isOriginOrDestinationFromFrance($carpoolProof)                                            // The trip must depart or arrive from the reference country
                && !$this->isDateAfterReferenceDate($carpoolProof->getStartDriverDate());                            // The user must not have made a short distance trip before the reference date
        });
    }
}
