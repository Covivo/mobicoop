<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Controller;

use App\Carpool\Service\ProposalManager;
use App\Carpool\Entity\Proposal;

/**
 * Controller class for proposal simple search.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalSimpleSearch
{
    private $proposalManager;

    public function __construct(ProposalManager $proposalManager)
    {
        $this->proposalManager = $proposalManager;
    }

    /**
     * This method is invoked when a simple search proposal is posted.
     * It returns the proposal posted with its matchings as subresources.
     *
     * @param Proposal $data
     * @return Proposal
     */
    public function __invoke(Proposal $data): Proposal
    {
        $data = $this->proposalManager->getMatchings($data);
        return $data;
    }
}
