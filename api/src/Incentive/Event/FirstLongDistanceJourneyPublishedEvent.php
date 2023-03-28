<?php

namespace App\Incentive\Event;

use App\Carpool\Entity\Proposal;
use Symfony\Contracts\EventDispatcher\Event;

class FirstLongDistanceJourneyPublishedEvent extends Event
{
    public const NAME = 'first_longDistanceJourney_published';

    /**
     * @var Proposal
     */
    private $_proposal;

    public function __construct(Proposal $proposal)
    {
        $this->_proposal = $proposal;
    }

    public function getProposal(): Proposal
    {
        return $this->_proposal;
    }
}
