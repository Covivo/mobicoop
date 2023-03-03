<?php

namespace App\Incentive\Service\Checker;

use App\Incentive\Service\LoggerService;

class SubscriptionChecker extends Checker
{
    public function __construct(LoggerService $loggerService)
    {
        parent::__construct($loggerService);
    }
}
