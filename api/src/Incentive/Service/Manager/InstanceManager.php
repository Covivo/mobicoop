<?php

namespace App\Incentive\Service\Manager;

use App\Incentive\Resource\EecInstance;

class InstanceManager
{
    /**
     * @var null|\DateTime
     */
    private $_eecServiceExpirationDate;

    /**
     * @var string[]
     */
    private $_subscriptionKeys;

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

    public function isLdSubscriptionAvailable(): bool
    {
        return $this->_currentEecInstance->isLdSubscriptionAvailable();
    }

    public function isSdSubscriptionAvailable(): bool
    {
        return $this->_currentEecInstance->isSdSubscriptionAvailable();
    }
}
