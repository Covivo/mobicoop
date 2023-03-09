<?php

namespace App\Incentive\Event;

use App\Carpool\Entity\CarpoolProof;
use Symfony\Contracts\EventDispatcher\Event;

class FirstShortDistanceJourneyPublishedEvent extends Event
{
    public const NAME = 'first_shortDistanceJourney_published';

    /**
     * @var CarpoolProof
     */
    private $_carpoolProof;

    public function __construct(CarpoolProof $carpoolProof)
    {
        $this->_carpoolProof = $carpoolProof;
    }

    public function getCarpoolProof(): CarpoolProof
    {
        return $this->_carpoolProof;
    }
}
