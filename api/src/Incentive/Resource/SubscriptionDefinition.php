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
    private $transitionalPeriodDuration;

    /**
     * @var null|\DateTimeInterface
     *
     * @Groups({"readSubscription"})
     */
    private $transitionalPeriodEndDate;

    /**
     * @var null|int
     *
     * @Groups({"readSubscription"})
     */
    private $version;

    /**
     * @var LongDistanceSuscription|ShortDistaceSubscription
     */
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

    /**
     * Get the value of transitionalPeriodDuration.
     */
    public function getTransitionalPeriodDuration(): ?int
    {
        return $this->transitionalPeriodDuration;
    }

    /**
     * Get the value of transitionalPeriodEndDate.
     */
    public function getTransitionalPeriodEndDate(): ?\DateTimeInterface
    {
        return $this->transitionalPeriodEndDate;
    }

    /**
     * Set the value of transitionalPeriodDuration.
     */
    protected function setTransitionalPeriodDuration(): self
    {
        $this->transitionalPeriodDuration = $this->definition::getTransitionalPeriodDuration();

        return $this;
    }

    /**
     * Set the value of transitionalPeriodEndDate.
     */
    protected function setTransitionalPeriodEndDate(): self
    {
        $this->transitionalPeriodEndDate = $this->definition::getTransitionalPeriodEndDate();

        return $this;
    }

    protected function setDeadline()
    {
        $this->deadline = $this->definition::getDeadline();
    }

    private function _build()
    {
        $this->version = $this->subscription->getVersion();

        $this->definition = array_values(array_filter($this->subscription->getAvailableDefinitions(), function ($defintion) {
            return $this->version === $defintion::getVersion();
        }))[0];

        $this->setDeadline();
        $this->setTransitionalPeriodDuration();
        $this->setTransitionalPeriodEndDate();
    }
}
