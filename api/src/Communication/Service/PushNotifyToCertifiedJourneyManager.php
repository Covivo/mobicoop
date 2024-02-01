<?php

namespace App\Communication\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PushNotifyToCertifiedJourneyManager
{
    /**
     * @var int
     */
    protected $_interval;

    public function __construct() {}

    /**
     * @param int|string $interval
     */
    public function execute($interval): bool
    {
        $this->_interval = is_string($interval)
            ? $this->_validatedInterval($interval) : $interval;

        return false;
    }

    protected function _validatedInterval(string $interval): int
    {
        if (!preg_match('/^\d+$/', $interval)) {
            throw new BadRequestHttpException('The given interval is not an integer.');
        }

        return intval($interval);
    }
}
