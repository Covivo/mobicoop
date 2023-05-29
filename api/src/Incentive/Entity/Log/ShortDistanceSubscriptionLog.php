<?php

namespace App\Incentive\Entity\Log;

use App\Incentive\Entity\ShortDistanceSubscription;
use Doctrine\ORM\Mapping as ORM;

/**
 * ShortDistanceSubscriptionLog.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class ShortDistanceSubscriptionLog extends Log
{
    /**
     * @var int The cee ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The LD subscription.
     *
     * @var ShortDistanceSubscription
     *
     * @ORM\ManyToOne(targetEntity=LongDistanceSubscription::class, inversedBy="logs")
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $subscription;

    public function __construct(ShortDistanceSubscription $subscription, $code, $content, $payload)
    {
        $this->setSubscription($subscription);

        parent::__construct($code, $content, $payload);
    }

    /**
     * Get id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the LD subscription.
     */
    public function getSubscription(): ShortDistanceSubscription
    {
        return $this->subscription;
    }

    /**
     * Set the LD subscription.
     *
     * @param ShortDistanceSubscription $subscription the LD subscription
     *
     * @return self
     */
    public function setSubscription(ShortDistanceSubscription $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }
}
