<?php

namespace App\Incentive\Controller;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Service\MobConnectSubscriptionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/incentives")
 */
class TestController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var MobConnectSubscriptionManager
     */
    private $_mobConnectSubscriptionManager;

    public function __construct(EntityManagerInterface $em, MobConnectSubscriptionManager $mobConnectSubscriptionManager)
    {
        $this->_em = $em;
        $this->_mobConnectSubscriptionManager = $mobConnectSubscriptionManager;
    }

    /**
     * @Route("/test")
     */
    public function test()
    {
        $carpoolProof = $this->_em->getRepository(CarpoolProof::class)->find(24);

        $this->_mobConnectSubscriptionManager->updateSubscription($carpoolProof);

        return new Response('Ok');
    }
}
