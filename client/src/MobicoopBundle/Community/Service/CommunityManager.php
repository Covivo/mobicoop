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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;

/**
 * Community management service.
 */
class CommunityManager
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
        $this->dataProvider->setClass(Community::class);
    }
    /**
     * Create a community
     *
     * @param Community
     *
     * @return Community|null
     */
    public function createCommunity(Community $community)
    {
        $response = $this->dataProvider->post($community);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }

    /**
    * Get all communities
    * @return array|null The communities found or null if not found.
    *
    */
    public function getCommunities()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
    /**
     * Get one community
     *
     * @return Community|null
     */
    public function getCommunity($id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
