<?php

namespace App\Incentive\Service\Validation;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Service\LoggerService;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserValidation extends Validation
{
    /**
     * @var TokenStorageInterface
     */
    private $_tokenStorage;

    public function __construct(LoggerService $loggerService, TokenStorageInterface $tokenStorageInterface)
    {
        $this->_tokenStorage = $tokenStorageInterface;

        parent::__construct($loggerService);
    }

    /**
     * The user has not made any valid long-distance journeys for 3 years.
     */
    public function isUserAccountReadyForSubscription(User $driver, bool $isLongDistance = true): bool
    {
        $this->setDriver($driver);

        $isUserValid = $this->isUserValid();
        $carpoolProofs = $this->_driver->getCarpoolProofsAsDriver();

        // The user is valid and he has not made any trips
        if ($isUserValid && (is_null($carpoolProofs) || empty($carpoolProofs))) {
            return true;
        }

        $filteredCarpoolProofs = $isLongDistance ? $this->_getCarpoolProofsForLongDistance($carpoolProofs) : $this->_getCarpoolProofsForShortDistance($carpoolProofs);

        if (!$isUserValid) {
            return false;
        }

        return empty($filteredCarpoolProofs);
    }

    public function isUserProperlyConnectToMob(User $driver): bool
    {
        $this->setDriver($driver);

        return
            !is_null($this->_driver->getMobConnectAuth())
            && $this->_driver->getMobConnectAuth()->getRefreshTokenExpiresDate() > new \DateTime('now');
    }

    public function isUserValidForEEC(User $user): bool
    {
        return $this->hasValidMobConnectAuth($user);
    }

    public function hasValidMobConnectAuth(?User $user): bool
    {
        /**
         * @var User $requester
         */
        $requester = is_null($user) ? $this->_tokenStorage->getToken()->getUser() : $user;

        if (is_null($requester->getMobConnectAuth())) {
            return false;
        }

        $now = new \DateTime('now');
        if (
            is_null($requester->getMobConnectAuth()->getRefreshTokenExpiresDate())
            || ($requester->getMobConnectAuth()->getRefreshTokenExpiresDate() < $now)
        ) {
            return false;
        }

        return true;
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
