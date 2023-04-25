<?php

namespace App\UserRelaunch\Controller;

use App\UserRelaunch\Service\RelaunchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/relaunches")
 */
class TestController extends AbstractController
{
    /**
     * @var RelaunchManager
     */
    private $_relaunchManager;

    public function __construct(RelaunchManager $relaunchManager)
    {
        $this->_relaunchManager = $relaunchManager;
    }

    /**
     * @Route("/test")
     */
    public function test()
    {
        $this->_relaunchManager->relaunchUsers();

        return new Response('Process processing is complete');
    }
}
