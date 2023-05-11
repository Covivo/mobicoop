<?php

namespace App\Incentive\Controller;

use App\Incentive\Entity\EecResponse;
use App\Incentive\Service\Manager\JourneyManager;
use App\Incentive\Service\Validation\SubscriptionValidation;
use App\Incentive\Service\Validation\UserValidation;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class JourneyController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

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
        SubscriptionValidation $subscriptionValidation,
        UserValidation $userValidation,
        JourneyManager $journeyManager,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ) {
        $this->_subscriptionValidation = $subscriptionValidation;
        $this->_userValidation = $userValidation;

        $this->_journeyManager = $journeyManager;

        $this->_em = $em;
        $this->_userRepository = $userRepository;
    }

    /**
     * @Route("/eec/journeys/process")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function proofsRecovery(Request $request)
    {
        $this->_subscriptionType = $request->get('type');
        $userId = $request->get('user');

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
            $users = $this->_userRepository->findUsersCeeSubscribed($this->_subscriptionType);

            foreach ($users as $user) {
                $this->_currentUser = $user;

                $this->_executeForUser();
            }
        }

        return new JsonResponse($this->_responseData);
    }

    private function _executeForUser()
    {
        $currentResponseData = new EecResponse($this->_currentUser);

        if (!$this->_userValidation->isUserValidForEEC($this->_currentUser)) {
            $currentResponseData->addError('The user mobConnect auth is not valid');
        }

        if (empty($currentResponseData->getErrors())) {
            $result = $this->_journeyManager->userProofsRecovery($this->_currentUser, $this->_subscriptionType);
        }

        if (!empty($currentResponseData->getErrors()) || $result) {
            array_push($this->_responseData, $currentResponseData);
        }
    }
}
