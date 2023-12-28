<?php

namespace Mobicoop\Bundle\MobicoopBundle\Eec\Controller;

use Mobicoop\Bundle\MobicoopBundle\Incentive\Service\EecManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class EecInstanceController extends AbstractController
{
    /**
     * @var EecManager
     */
    private $_eecManager;

    public function __construct(EecManager $eecManager)
    {
        $this->_eecManager = $eecManager;
    }

    public function getEecInstance(Request $request)
    {
        if (is_null($this->getUser())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse($this->_eecManager->getEecInstance());
    }
}
