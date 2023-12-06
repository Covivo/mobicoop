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

    public function __construct(array $subscriptionKeys, string $eecServiceExpirationDate)
    {
        $this->setSubscriptionKeys($subscriptionKeys);

        if (!empty($eecServiceExpirationDate)) {
            $this->_eecServiceExpirationDate = new \DateTime($eecServiceExpirationDate.' 23:59:59');
        }

        $this->_currentEecInstance = new EecInstance();

        $this->_buildEecInstance();
    }

    public function getEecInstance(): EecInstance
    {
        return $this->_currentEecInstance;
    }

    public function setSubscriptionKeys(array $subscriptionKeys): self
    {
        $this->_subscriptionKeys = !empty($subscriptionKeys['ld']) && !empty($subscriptionKeys['sd'])
            ? $subscriptionKeys : [];

        return $this;
    }

    private function _buildEecInstance()
    {
        $this->_currentEecInstance->setAvailable($this->_isServiceOpened());
        if (!is_null($this->_eecServiceExpirationDate)) {
            $this->_currentEecInstance->setExpirationDate($this->_eecServiceExpirationDate);
        }
    }

    private function _isServiceOpened(): bool
    {
        if (is_null($this->_eecServiceExpirationDate) && !empty($this->_subscriptionKeys)) {
            return true;
        }

        if (!is_null($this->_eecServiceExpirationDate) && !empty($this->_subscriptionKeys)) {
            $now = new \DateTime('now');

            return $now < $this->_eecServiceExpirationDate;
        }

        return false;
    }
}
