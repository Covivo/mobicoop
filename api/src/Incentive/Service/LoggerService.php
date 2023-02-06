<?php

namespace App\Incentive\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LoggerService
{
    private const LOG_TYPES = ['error', 'notice', 'info', 'debug'];

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(RequestStack $requestStack, LoggerInterface $loggerInterface)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_logger = $loggerInterface;
    }

    public function log(string $msg, string $type = 'info', ?bool $forced = false)
    {
        if ($this->_areLogsOpen() || true === $forced) {
            $this->_logger->{$type}('TEST-'.$msg);
        }
    }

    private function _areLogsOpen(): bool
    {
        $param = $this->_request->get('log');

        return is_null($param) || 'false' === $param ? false : true;
    }
}
