<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Resource\Incentive;

class IncentiveManager
{
    public function getIncentives(): array
    {
        return [
            new Incentive('64'),
        ];
    }

    public function getIncentive(string $incentive_id): ?Incentive
    {
        return new Incentive($incentive_id);
    }
}
