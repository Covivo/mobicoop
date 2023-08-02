<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\EecResponse;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\Payment\Repository\CarpoolItemRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class JourneyRecoveryManager extends JourneyManager
{
    /**
     * @var array
     */
    private $_responseData = [];

    /**
     * @var string
     */
    private $_subscriptionType;

    /**
     * @var SubscriptionValidation
     */
    private $_subscriptionValidation;

    /**
     * @var User
     */
    private $_currentUser;

    /**
     * @var UserRepository
     */
    private $_userRepository;

    /**
     * @var UserValidation
     */
    private $_userValidation;

    public function __construct(
        CarpoolProofRepository $carpoolProofRepository,
        CarpoolItemRepository $carpoolItemRepository,
        EntityManagerInterface $em,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        TimestampTokenManager $timestampTokenManager,
        SubscriptionValidation $subscriptionValidation,
        UserRepository $userRepository,
        UserValidation $userValidation,
        LongDistanceJourneyRepository $longDistanceJourneyRepository,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($carpoolProofRepository, $carpoolItemRepository, $em, $journeyValidation, $loggerService, $honourCertificateService, $timestampTokenManager, $longDistanceJourneyRepository, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userRepository = $userRepository;
        $this->_userValidation = $userValidation;
    }

    public function executeProofsRecovery(string $type, ?int $userId)
    {
        $this->_subscriptionType = $type;

        $this->_subscriptionValidation->checkSubscriptionTypeValidity($this->_subscriptionType);

        if (!is_null($userId)) {
            $this->_currentUser = $this->_em->getRepository(User::class)->find($userId);
            if (!is_null($this->_currentUser)) {
                $this->_executeForUser();
            }
        } else {
            $users = $this->_userRepository->findUsersCeeSubscribed();

            foreach ($users as $user) {
                $this->_currentUser = $user;

                $this->_executeForUser();
            }
        }

        return $this->_responseData;
    }

    private function _executeForUser()
    {
        $currentResponseData = new EecResponse($this->_currentUser);

        $errors = $this->_userValidation->isUserValidForEEC($this->_currentUser, $this->_subscriptionType);

        // We recover the missing timestamp tokens available at moBConnect
        $this->_timestampTokenManager->setMissingSubscriptionTimestampTokens(
            MobConnectManager::LONG_SUBSCRIPTION_TYPE === $this->_subscriptionType
                ? $this->_currentUser->getLongDistanceSubscription() : $this->_currentUser->getShortDistanceSubscription(),
            Log::TYPE_VERIFY
        );

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $currentResponseData->addError($error);
            }
        }

        if (empty($currentResponseData->getErrors())) {
            $result = $this->userProofsRecovery($this->_currentUser, $this->_subscriptionType);
        }

        if (!empty($currentResponseData->getErrors()) || $result) {
            array_push($this->_responseData, $currentResponseData);
        }
    }
}
