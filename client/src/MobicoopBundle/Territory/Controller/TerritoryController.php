<?php

namespace Mobicoop\Bundle\MobicoopBundle\Territory\Controller;

use Mobicoop\Bundle\MobicoopBundle\Territory\Service\TerritoryManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class TerritoryController extends AbstractController
{
    private $_territoryManager;

    public function __construct(TerritoryManager $territoryManager)
    {
        $this->_territoryManager = $territoryManager;
    }

    public function getTerritory(Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($this->_territoryManager->getTerritory($request->get('id')));
        }

        return new JsonResponse();
    }
}
