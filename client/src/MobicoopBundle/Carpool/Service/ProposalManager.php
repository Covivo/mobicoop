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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Proposal management service.
 */
class ProposalManager
{
    private $dataProvider;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Proposal::class);
    }
 
    /**
     * Get a proposal for a user
     *
     * @param int $id
     * @return Proposal|null The proposal found or null if not found.
     */
    public function getProposal(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        return $response->getValue();
    }
    
    /**
     * Delete a proposal
     *
     * @param int $id The id of the proposal to delete
     *
     * @param array|null $data
     * @return boolean The result of the deletion.
     */
    public function deleteProposal(int $id, ?array $data)
    {
        $response = $this->dataProvider->delete($id, $data);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }
}
