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

use App\Auth\Interfaces\AuthRuleInterface;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;

/**
 *  Check that the requester is a manager of the related Community.
 */
class CommunityManager implements AuthRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requester, $item, $params)
    {
        var_dump('communityManager');
        /**
         * @var Community $community
         */
        $community = $params['community'];

        $communityUsers = $community->getCommunityUsers();

        foreach ($communityUsers as $communityUser) {
            var_dump($communityUser->getUser()->getId() == $requester->getId() && CommunityUser::STATUS_ACCEPTED_AS_MODERATOR == $communityUser->getStatus());
            if ($communityUser->getUser()->getId() == $requester->getId() && CommunityUser::STATUS_ACCEPTED_AS_MODERATOR == $communityUser->getStatus()) {
                var_dump('true');

                return true;
            }
        }
        var_dump('false');

        return false;
    }
}
