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
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
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
    
    /**
     * Get all proposals for a user
     *
     * @return array|null The proposals found or null if not found.
     */
    public function getProposals(User $user)
    {
        // we will make the request on the User instead of the Proposal
        $this->dataProvider->setClass(User::class);
        $response = $this->dataProvider->getSubCollection($user->getId(), Proposal::class);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
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
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all matchings for a user proposal
     *
     * @return array|null The matchings found or null if not found.
     */
    public function getMatchings(Proposal $proposal)
    {
        // we will make the request on the Matching instead of the Proposal
        if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER) {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_requests");
        } else {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_offers");
        }
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get all matchings for a search.
     *
     * @param float $origin_latitude        The origin latitude
     * @param float $origin_longitude       The origin longitude
     * @param float $destination_latitude   The destination latitude
     * @param float $destination_longitude  The destination longitude
     * @param \Datetime $date               The date and time in a Datetime object
     * @return array|null The matchings found or null if not found.
     */
    public function getMatchingsForSearch(float $origin_latitude, float $origin_longitude, float $destination_latitude, float $destination_longitude, \Datetime $date)
    {
        // we set the params
        $params = [
            "origin_latitude" => $origin_latitude,
            "origin_longitude" => $origin_longitude,
            "destination_latitude" => $destination_latitude,
            "destination_longitude" => $destination_longitude,
            "date" => $date->format('Y-m-d\TH:i:s\Z')
        ];
        // we will make a request on the Matching resource, so we chan ge it here (because the resource is initialized to Proposal in this class constructor)
        $this->dataProvider->setClass(Matching::class);
        $response = $this->dataProvider->getSubCollection(null, Matching::class, "search", $params);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Delete a proposal
     *
     * @param int $id The id of the proposal to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteProposal(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }
}
