<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\ManualProcessingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/eec")
 */
class ManualProcessingController extends AbstractController
{
    /**
     * @var ManualProcessingService
     */
    private $_manualProcessingService;

    public function __construct(ManualProcessingService $manualProcessingService)
    {
        $this->_manualProcessingService = $manualProcessingService;
    }

    /**
     * @Route("/manual-processing")
     */
    public function manualProcessing()
    {
        $this->_manualProcessingService->execute();

        return new Response('Processing completed');
    }
}
