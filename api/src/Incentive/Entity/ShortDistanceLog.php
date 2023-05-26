<?php

namespace App\Incentive\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShortDistanceLog.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 */
class ShortDistanceLog extends CommitmentRequestLog
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
     * The short journey.
     *
     * @var ShortDistanceJourney
     *
     * @ORM\ManyToOne(targetEntity=ShortDistanceJourney::class, inversedBy="logs")
     *
     * @ORM\JoinColumn(nullable=true)
     */
    private $journey;

    public function __construct(ShortDistanceJourney $journey, $code, $content, $payload)
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
     * Get the short journey.
     *
     * @return ShortDistanceJourney
     */
    public function getJourney()
    {
        return $this->journey;
    }

    /**
     * Set the short journey.
     *
     * @param ShortDistanceJourney $journey the short journey
     *
     * @return self
     */
    public function setJourney(ShortDistanceJourney $journey)
    {
        $this->journey = $journey;

        return $this;
    }
}
