<?php

namespace App\Incentive\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LongDistanceLog.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class LongDistanceLog extends CommitmentRequestLog
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
     * The LD journey.
     *
     * @var LongDistanceJourney
     *
     * @ORM\ManyToOne(targetEntity=LongDistanceJourney::class, inversedBy="logs")
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $journey;

    public function __construct(LongDistanceJourney $journey, $code, $content, $payload)
    {
        $this->setJourney($journey);

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
     * Get the LD journey.
     *
     * @return LongDistanceJourney
     */
    public function getJourney()
    {
        return $this->journey;
    }

    /**
     * Set the LD journey.
     *
     * @param LongDistanceJourney $journey the LD journey
     *
     * @return self
     */
    public function setJourney(LongDistanceJourney $journey)
    {
        $this->journey = $journey;

        return $this;
    }
}
