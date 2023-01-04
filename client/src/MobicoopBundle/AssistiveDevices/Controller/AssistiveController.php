<?php

namespace Mobicoop\Bundle\MobicoopBundle\AssistiveDevices\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AssistiveController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function assistiveDevices()
    {
        return $this->render('@Mobicoop/assistiveDevices/assistive.html.twig');
    }
}
