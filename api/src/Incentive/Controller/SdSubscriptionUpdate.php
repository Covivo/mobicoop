<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\Manager\JourneyManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SdSubscriptionUpdate
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var JourneyManager
     */
    private $_journeyManager;

    public function __construct(RequestStack $requestStack, EntityManagerInterface $em, JourneyManager $journeyManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_em = $em;

        $this->_journeyManager = $journeyManager;
    }

    public function __invoke(ShortDistanceSubscription $subscription)
    {
        /**
         * @var CarpoolProof
         */
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find($this->_request->get('carpool_proof'));

        if (is_null($carpoolProof)) {
            throw new NotFoundHttpException('The requested proof was not found');
        }

        $this->_journeyManager->validationOfProof($carpoolProof);

        return $subscription;
    }
}
