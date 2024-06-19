<?php

namespace App\Incentive\Service\Provider;

use App\Carpool\Entity\CarpoolProof;
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

    /**
     * @return null|LongDistanceJourney|ShortDistanceJourney
     */
    public function getJourneyFromCarpoolProof(CarpoolProof $carpoolProof)
    {
        return !is_null($carpoolProof->getMobConnectShortDistanceJourney())
            ? $carpoolProof->getMobConnectShortDistanceJourney()
            : $this->_ldJourneyRepository->findOneByCarpoolItemOrProposal(
                $carpoolProof->getCarpoolItem(),
                ProposalProvider::getProposalFromCarpoolItem($carpoolProof->getCarpoolItem(), ProposalProvider::DRIVER)
            );
    }

    public function getJourneyFromCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceJourney
    {
        return $this->_ldJourneyRepository->findOneByCarpoolItem($carpoolItem);
    }
}
