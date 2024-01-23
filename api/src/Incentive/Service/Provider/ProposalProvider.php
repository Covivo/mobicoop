<?php

namespace App\Incentive\Service\Provider;

use App\Carpool\Entity\Proposal;
use App\Incentive\Entity\LongDistanceJourney;
use App\Payment\Entity\CarpoolItem;

class ProposalProvider
{
    public const DRIVER = 1;

    public static function getProposalFromCarpoolItem(?CarpoolItem $carpoolItem, int $carpoolerType): ?Proposal
    {
        if (is_null($carpoolItem)) {
            return null;
        }

        $proposal = null;

        $user = static::DRIVER === $carpoolerType ? $carpoolItem->getCreditorUser() : $carpoolItem->getDebtorUser();

        switch ($user->getId()) {
            case $carpoolItem->getAsk()->getMatching()->getProposalOffer()->getUser()->getId():
                $proposal = $carpoolItem->getAsk()->getMatching()->getProposalOffer();

                break;

            case $carpoolItem->getAsk()->getMatching()->getProposalRequest()->getUser()->getId():
                $proposal = $carpoolItem->getAsk()->getMatching()->getProposalRequest();

                break;
        }

        return $proposal;
    }

    public static function getProposalFromLdJourney(LongDistanceJourney $journey): ?Proposal
    {
        return !is_null($journey->getInitialProposal())
            ? $journey->getInitialProposal()
            : (
                !is_null($journey->getCarpoolItem())
                && !is_null($journey->getCarpoolItem()->getAsk())
                && !is_null($journey->getCarpoolItem()->getAsk()->getMatching())
                && !is_null($journey->getCarpoolItem()->getAsk()->getMatching()->getProposalOffer())
                ? $journey->getCarpoolItem()->getAsk()->getMatching()->getProposalOffer()
                : null
            );
    }
}
