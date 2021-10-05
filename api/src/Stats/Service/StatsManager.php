<?php
/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\Stats\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Community\Repository\CommunityRepository;
use App\Stats\Entity\Indicator;
use App\User\Repository\UserRepository;
use DateTime;

/**
 * Statistics manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StatsManager
{
    private $proposalRepository;
    private $communityRepository;
    private $carpoolProofRepository;
    private $userRepository;
    private $askRepository;
    private $indicators;

    public function __construct(
        ProposalRepository $proposalRepository,
        CommunityRepository $communityRepository,
        CarpoolProofRepository $carpoolProofRepository,
        UserRepository $userRepository,
        AskRepository $askRepository
    ) {
        $this->proposalRepository = $proposalRepository;
        $this->communityRepository = $communityRepository;
        $this->carpoolProofRepository = $carpoolProofRepository;
        $this->userRepository = $userRepository;
        $this->askRepository = $askRepository;
    }

    /**
     * Get the Home indicators
     *
     * @return Indicator[]
     */
    public function getHomeIndicators(): array
    {
        // WARNING : It's a first version. We need to elaborate the system with a list of indicator, possibly in database

        $this->addAvalaibleAdsNumberIndicator();

        $this->addUsersNumberIndicator();

        $this->addCommunitiesNumberIndicator();

        $this->addAsksNumberIndicator();

        return $this->indicators;
    }

    /**
     * Add an Indicator in the current array $indicators
     *
     * @param string $label     Indicator's label
     * @param float $value      Indicator's value
     * @return array Updated current array of Indicators
     */
    private function addIndicator(string $label, float $value)
    {
        $indicator = new Indicator();
        $indicator->setLabel($label);
        $indicator->setValue($value);
        $this->indicators[] = $indicator;
    }

    /**
     * Add the number of avalaibles ads in indicator
     *
     */
    private function addAvalaibleAdsNumberIndicator()
    {
        $this->addIndicator("available_ads", $this->proposalRepository->countAvailableAds());
    }

    /**
     * Add the number of users in indicator
     *
     */
    private function addUsersNumberIndicator()
    {
        $this->addIndicator("users", $this->userRepository->countUsers());
    }

    /**
     * Add the number of communities in indicator
     *
     */
    private function addCommunitiesNumberIndicator()
    {
        $this->addIndicator("communities_count", $this->communityRepository->countCommunities());
    }

    /**
     * Add the number of the aks in indicator
     *
     */
    private function addAsksNumberIndicator()
    {
        $this->addIndicator("carpoolers_connected", $this->askRepository->countAsks());
    }

    /**
     * Add the number of the last ads published in the last month in indicator
     *
     */
    private function addLastMonthAdsNumberIndicator()
    {
        // last month published ad
        $now = new \DateTime();
        $lastMonth = $now->modify('-1 months');
         
        $startDate = DateTime::createFromFormat("d/n/Y h:i:s", "01/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");
        $endDate = DateTime::createFromFormat("d/n/Y h:i:s", $lastMonth->format('t')."/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");
        $this->addIndicator("ads_last_month", $this->proposalRepository->countProposalsBetweenCreateDate($startDate, $endDate));
    }

    /**
     * Add the number of active users in indicator
     *
     */
    private function addActiveUsersNumberIndicator()
    {
        // Active users (connection in the last 6 months)
        $this->addIndicator("users_active", $this->userRepository->countActiveUsers());
    }

    /**
     * Add the number of carpool proofs published in the last month in indicator
     *
     */
    private function addCarpoolProofsLastMonthNumberIndicator()
    {
        // Last month proofs
        $now = new \DateTime();
        $lastMonth = $now->modify('-1 months');

        $startDate = DateTime::createFromFormat("d/n/Y h:i:s", "01/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");
        $endDate = DateTime::createFromFormat("d/n/Y h:i:s", $lastMonth->format('t')."/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");

        $proofs = $this->carpoolProofRepository->findByTypesAndPeriod([CarpoolProof::TYPE_LOW,CarpoolProof::TYPE_MID,CarpoolProof::TYPE_HIGH], $startDate, $endDate, [CarpoolProof::STATUS_SENT]);
        $this->addIndicator("carpool_proofs_last_month", (is_array($proofs)) ? count($proofs) : 0);
    }
}
