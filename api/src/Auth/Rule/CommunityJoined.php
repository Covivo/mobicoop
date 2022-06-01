<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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
 */

namespace App\Auth\Rule;

use App\App\Entity\App;
use App\Auth\Interfaces\AuthRuleInterface;
use App\Community\Entity\Community;

/**
 *  Check that the requester has joined the related Community.
 */
class CommunityJoined implements AuthRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requester, $item, $params): bool
    {
        if (!isset($params['community'])) {
            return false;
        }

        if (!($params['community'] instanceof Community)) {
            return false;
        }

        /**
         * @var Community $community
         */
        $community = $params['community'];
        // We check if this is a secured Community
        // If so, we check if the requester is a member of this community
        if (count($community->getCommunitySecurities()) > 0) {
            // An app can't see a secured community
            if ($requester instanceof App) {
                return false;
            }

            $communityUsers = $community->getCommunityUsers();
            foreach ($communityUsers as $communityUser) {
                if ($communityUser->getUser()->getId() == $requester->getId()) {
                    return true;
                }
            }
        } else {
            // The community is not secured. No more condition to grant access
            return true;
        }

        return false;
    }
}
