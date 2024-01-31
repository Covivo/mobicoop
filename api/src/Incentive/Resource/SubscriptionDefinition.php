<?php

namespace App\Incentive\Resource;

use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use Symfony\Component\Serializer\Annotation\Groups;

class SubscriptionDefinition
{
    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readSubscription"})
     */
    private $deadline;

    /**
     * @var null|int
     *
     * @Groups({"readSubscription"})
     */
    private $version;

    private $subscription;

    private $definition;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct($subscription)
    {
        $this->subscription = $subscription;

        $this->_build();
    }

    /**
     * Get the value of deadline.
     */
    public function getDeadline(): ?\DateTimeInterface
    {
        return $this->deadline;
    }

    /**
     * Get the value of version.
     */
    public function getVersion(): ?int
    {
        return $this->version;
    }

    protected function setDeadline()
    {
        $this->definition = array_values(array_filter($this->subscription->getAvailableDefinitions(), function ($defintion) {
            return $this->version === $defintion::getVersion();
        }))[0];

        $this->deadline = $this->definition::getDeadline();
    }

    private function _build()
    {
        $this->version = $this->subscription->getVersion();

        $this->setDeadline();
    }
}
