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

namespace App\Community\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Community\Entity\Community;

/**
 * Event sent when a user wants to join a community with a manual validation
 * @author Celine Jacquet <celine.jacquet@mobicoop.org>
 */
class CommunityNewMembershipRequestEvent extends Event
{
    public const NAME = 'community_new_membership_request';

    protected $community;

    public function __construct(Community $community)
    {
        $this->community = $community;
 
    }

    public function getCommunity()
    {
        return $this->community;
    }
}

