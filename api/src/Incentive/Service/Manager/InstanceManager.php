<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Resource\EecInstance;

class InstanceManager
{
    /**
     * @var EecInstance
     */
    private $_currentEecInstance;

    public function __construct($eecServiveConfiguration)
    {
        $this->_currentEecInstance = new EecInstance($eecServiveConfiguration);
    }

    public function getEecInstance(): EecInstance
    {
        return $this->_currentEecInstance;
    }

    public function isEecServiceAvailable(): bool
    {
        return $this->getEecInstance()->isAvailable();
    }

    public function isLongSubscriptionAvailable(): bool
    {
        return $this->_currentEecInstance->isLongDistanceSubscriptionAvailable();
    }

    public function isShortSubscriptionAvailable(): bool
    {
        return $this->_currentEecInstance->isShortDistanceSubscriptionAvailable();
    }
}
