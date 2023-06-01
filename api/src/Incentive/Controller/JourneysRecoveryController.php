<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\JourneyRecoveryManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec/recovery")
 */
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
     * @Route("/journeys")
     *
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function proofsRecovery(Request $request)
    {
        $type = $request->get('type');

        if (is_null($type)) {
            throw new BadRequestHttpException('The mandatory type parameter is missing.');
        }

        return new JsonResponse($this->_journeyRecoveryManager->executeProofsRecovery($type, $request->get('user')));
    }
}
