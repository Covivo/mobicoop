<?php

namespace App\Incentive\Resource\Provider;

class MobConnectProvider extends EecProvider
{
    public function __construct(array $provider)
    {
        $this->_build($provider);
    }
}
