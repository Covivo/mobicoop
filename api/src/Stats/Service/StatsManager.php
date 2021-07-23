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
use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Repository\ProposalRepository;
use App\Community\Repository\CommunityRepository;
use App\Stats\Entity\Indicator;
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

    public function __construct(
        ProposalRepository $proposalRepository,
        CommunityRepository $communityRepository,
        CarpoolProofRepository $carpoolProofRepository
    ) {
        $this->proposalRepository = $proposalRepository;
        $this->communityRepository = $communityRepository;
        $this->carpoolProofRepository = $carpoolProofRepository;
    }

    /**
     * Get the Home indicators
     *
     * @return Indicator[]
     */
    public function getHomeIndicators(): array
    {
        // WARNING : It's a first version. We need to elaborate the system with a list of indicator, possibly in database

        $indicators = [];

        // last month published ad
        $now = new \DateTime();
        $lastMonth = $now->modify('-1 months');
        
        $startDate = DateTime::createFromFormat("d/n/Y h:i:s", "01/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");
        $endDate = DateTime::createFromFormat("d/n/Y h:i:s", $lastMonth->format('t')."/".$lastMonth->format('n')."/".$lastMonth->format('Y')."00:00:00");
        $proposals = $this->proposalRepository->findBetweenCreateDate($startDate, $endDate);
        $indicators = $this->addIndicator($indicators, "proposals_last_month", (is_array($proposals)) ? count($proposals) : 0);

        // Active users
        // TO DO : What's an active User????
        $indicators = $this->addIndicator($indicators, "users_active", 0);

        // Number of communities
        $communities = $this->communityRepository->findAll();
        $indicators = $this->addIndicator($indicators, "communities_count", (is_array($communities)) ? count($communities) : 0);

        // last month proofs
        $proofs = $this->carpoolProofRepository->findByTypesAndPeriod([CarpoolProof::TYPE_LOW,CarpoolProof::TYPE_MID,CarpoolProof::TYPE_HIGH], $startDate, $endDate, [CarpoolProof::STATUS_SENT]);
        $indicators = $this->addIndicator($indicators, "carpool_proofs_last_month", (is_array($proofs)) ? count($proofs) : 0);

        return $indicators;
    }

    /**
     * Add an Indicator in the current array $indicators
     *
     * @param array $indicators Current array of Iindicators
     * @param string $label     Indicator's label
     * @param float $value      Indicator's value
     * @return array Updated current array of Indicators
     */
    private function addIndicator(array $indicators, string $label, float $value): array
    {
        $indicator = new Indicator();
        $indicator->setLabel($label);
        $indicator->setValue($value);
        $indicators[] = $indicator;
        return $indicators;
    }
}
