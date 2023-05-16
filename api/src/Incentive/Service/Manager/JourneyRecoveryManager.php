<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Repository\CarpoolProofRepository;
use App\Incentive\Entity\EecResponse;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use App\Incentive\Service\Validation\JourneyValidation;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
        EntityManagerInterface $em,
        EventDispatcherInterface $eventDispatcher,
        JourneyValidation $journeyValidation,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        SubscriptionValidation $subscriptionValidation,
        UserRepository $userRepository,
        UserValidation $userValidation,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($carpoolProofRepository, $em, $eventDispatcher, $journeyValidation, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);

        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userRepository = $userRepository;
        $this->_userValidation = $userValidation;
    }

    public function executeProofsRecovery(string $type, ?int $userId)
    {
        $this->_subscriptionType = $type;

        if (is_null($this->_subscriptionType)) {
            throw new BadRequestHttpException('The type parameter is required');
        }
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

        if (!$this->_userValidation->isUserValidForEEC($this->_currentUser)) {
            $currentResponseData->addError('The user mobConnect auth is not valid');
        }

        if (empty($currentResponseData->getErrors())) {
            $result = $this->userProofsRecovery($this->_currentUser, $this->_subscriptionType);
        }

        if (!empty($currentResponseData->getErrors()) || $result) {
            array_push($this->_responseData, $currentResponseData);
        }
    }
}
