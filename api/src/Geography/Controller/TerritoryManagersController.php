<?php

namespace App\Geography\Controller;

use App\Geography\Repository\TerritoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TerritoryManagersController extends AbstractController
{
    /**
     * @var Request
     */
    private $_request;

    /**
     * @var TerritoryRepository
     */
    private $_territoryRepository;

    public function __construct(RequestStack $requestStack, TerritoryRepository $territoryRepository)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_territoryRepository = $territoryRepository;
    }

    public function __invoke()
    {
        return new JsonResponse($this->_territoryRepository->findManagers(intval($this->_request->get('id'))));
    }
}
