<?php

namespace App\Incentive\Event;

use App\Incentive\Entity\ShortDistanceJourney;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a first long distance journey is validated by the RPC.
 */
class LastShortDistanceJourneyValidatedEvent extends Event
{
    public const NAME = 'last_short_distance_journey_validated';

    protected $journey;

    public function __construct(ShortDistanceJourney $journey)
    {
        $this->journey = $journey;
    }

    public function getJourney(): ShortDistanceJourney
    {
        return $this->journey;
    }
}
