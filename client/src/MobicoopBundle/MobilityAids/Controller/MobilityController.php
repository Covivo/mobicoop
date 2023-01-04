<?php

namespace Mobicoop\Bundle\MobicoopBundle\MobilityAids\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MobilityController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function mobilityAid()
    {
        return $this->render('@Mobicoop/mobilityAids/aids.html.twig');
    }
}
