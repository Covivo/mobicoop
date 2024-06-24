<?php

namespace App\Incentive\Service\Provider;

use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceJourney;
use App\Incentive\Repository\LongDistanceJourneyRepository;
use App\Incentive\Repository\ShortDistanceJourneyRepository;
use App\Payment\Entity\CarpoolItem;

class JourneyProvider
{
    /**
     * @var LongDistanceJourneyRepository
     */
    private $_ldRepository;

    /**
     * @var ShortDistanceJourneyRepository
     */
    private $_sdRepository;

    public function __construct(LongDistanceJourneyRepository $ldRepository, ShortDistanceJourneyRepository $sdRepository = null)
    {
        $this->_ldRepository = $ldRepository;
        $this->_sdRepository = $sdRepository;
    }

    public function getJourneyFromCarpoolItem(CarpoolItem $carpoolItem): ?LongDistanceJourney
    {
        $journey = $this->_ldRepository->findOneByCarpoolItem($carpoolItem);

        if (is_null($journey)) {
            $initialProposal = !is_null($carpoolItem->getAsk())
                && !is_null($carpoolItem->getAsk()->getMatching())
                && !is_null($carpoolItem->getAsk()->getMatching()->getProposalOffer())
                    ? $carpoolItem->getAsk()->getMatching()->getProposalOffer()
                    : null;

            if (!is_null($initialProposal)) {
                $journey = $this->_ldRepository->findOneBy(['initialProposal' => $initialProposal]);
            }
        }

        return $journey;
    }

    /**
     * @return null|LongDistanceJourney|ShortDistanceJourney
     */
    public function getJourneyFromCarpoolProof(CarpoolProof $carpoolProof)
    {
        $carpoolItem = $carpoolProof->getCarpoolItem();

        $journey = !is_null($carpoolItem) ? $this->getJourneyFromCarpoolItem($carpoolItem) : null;

        if (is_null($journey) && !is_null($this->_sdRepository)) {
            $journey = $this->_sdRepository->findOneByCarpoolProof($carpoolProof);
        }

        return $journey;
    }
}
