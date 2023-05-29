<?php

namespace App\Incentive\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LongDistanceSubscriptionLog.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class LongDistanceSubscriptionLog extends CommitmentRequestLog
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
     * @var LongDistanceSubscription
     *
     * @ORM\ManyToOne(targetEntity=LongDistanceSubscription::class, inversedBy="logs")
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $subscription;

    public function __construct(LongDistanceSubscription $subscription, $code, $content, $payload)
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
     *
     * @return LongDistanceSubscription
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * Set the LD subscription.
     *
     * @param LongDistanceSubscription $subscription the LD subscription
     *
     * @return self
     */
    public function setSubscription(LongDistanceSubscription $subscription)
    {
        $this->subscription = $subscription;

        return $this;
    }
}
