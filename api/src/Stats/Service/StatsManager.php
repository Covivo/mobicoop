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

use App\Carpool\Repository\ProposalRepository;
use App\Stats\Entity\Indicator;

/**
 * Statistics manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StatsManager
{
    private $proposalRepository;

    public function __construct(ProposalRepository $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    /**
     * Get the Home indicators
     *
     * @return Indicator[]
     */
    public function getHomeIndicators(): array
    {
        // WARNING : It's a first version. We need to elaborate the system with a list of indicator, possibly in database

        // last month published ad

        
        return [new Indicator()];
    }
}
