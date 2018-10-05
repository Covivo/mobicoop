<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Service;

use App\Entity\Proposal;

/**
 * Proposal management service.
 */
class ProposalManager
{
    private $dataProvider;
    private $deserializer;
    
    public function __construct(DataProvider $dataProvider, Deserializer $deserializer)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Proposal::class);
    }
    
    /**
     * Create a proposal
     *
     * @param Proposal $proposal The proposal to create
     *
     * @return Proposal|null The proposal created or null if error.
     */
    public function createProposal(Proposal $proposal)
    {
        $response = $this->dataProvider->post($proposal);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
}
