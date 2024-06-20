<?php

namespace App\Incentive\Service\Provider;

use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Payment\Entity\CarpoolItem;

class JourneyProvider
{
    /**
     * @var LongDistanceJOurneyRepository
     */
    private $_ldJourneyRepository;

    public function __construct(LongDistanceJourneyRepository $ldRepository)
    {
        $this->_ldJourneyRepository = $ldRepository;
    }

    public function getJourneyFromCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceJourney
    {
        return $this->_ldJourneyRepository->findOneByCarpoolItem($carpoolItem);
    }
}
