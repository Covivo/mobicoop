<?php

namespace App\Incentive\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class LoggerService
{
    public const TYPE_INFO = 'info';

    /**
     * @var bool
     */
    private $_globalForcingLogs;

    /**
     * @var LoggerInterface
     */
    private $_logger;

    /**
     * @var Request
     */
    private $_request;

    public function __construct(RequestStack $requestStack, LoggerInterface $loggerInterface, bool $globalForcingLogs)
    {
        $this->_request = $requestStack->getCurrentRequest();
        $this->_logger = $loggerInterface;
        $this->_globalForcingLogs = $globalForcingLogs;
    }

    public function log(string $msg, string $type = self::TYPE_INFO, ?bool $forced = false)
    {
        if (true === $this->_globalForcingLogs || $this->_areLogsOpen() || true === $forced) {
            $this->_logger->{$type}('TEST-'.$msg);
        }
    }

    private function _areLogsOpen(): bool
    {
        $param = !is_null($this->_request) ? $this->_request->get('log') : null;

        return !isset($param) || is_null($param) || 'false' === $param ? false : true;
    }
}
