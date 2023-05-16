<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\JourneyRecoveryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class JourneysRecoveryController extends AbstractController
{
    /**
     * @var JourneyRecoveryManager
     */
    private $_journeyRecoveryManager;

    public function __construct(JourneyRecoveryManager $journeyRecoveryManager)
    {
        $this->_journeyRecoveryManager = $journeyRecoveryManager;
    }

    /**
     * @Route("/eec/journeys/process")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function proofsRecovery(Request $request)
    {
        return new JsonResponse($this->_journeyRecoveryManager->executeProofsRecovery($request->get('type'), $request->get('user')));
    }
}
