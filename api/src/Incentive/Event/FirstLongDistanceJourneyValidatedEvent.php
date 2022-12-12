<?php

namespace App\Incentive\Event;

use App\Incentive\Entity\LongDistanceJourney;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a first long distance journey is validated by the RPC.
 */
class FirstLongDistanceJourneyValidatedEvent extends Event
{
    public const NAME = 'first_long_distance_journey_validated';

    protected $journey;

    public function __construct(LongDistanceJourney $journey)
    {
        $this->journey = $journey;
    }

    public function getJourney(): LongDistanceJourney
    {
        return $this->journey;
    }
}
