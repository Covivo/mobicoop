<?php

namespace App\Incentive\Controller;

use App\Incentive\Service\Manager\IncentiveManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class IncentivesController
{
    private const ALLOWED_PARAM = 'incentive_id';

    /**
     * @var IncentiveManager
     */
    private $_incentiveManager;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(RequestStack $requestStack, IncentiveManager $incentiveManager)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_incentiveManager = $incentiveManager;
    }

    public function __invoke(array $data)
    {
        return !is_null($this->_request->get(self::ALLOWED_PARAM))
            ? $this->_incentiveManager->getMobConnectIncentive($this->_request->get(self::ALLOWED_PARAM))
            : $this->_incentiveManager->getMobConnectIncentives();
    }
}
