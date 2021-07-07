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

namespace App\Community\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Community\Entity\Community;
use App\User\Entity\User;

/**
 * Event sent when a user join a community with no validation
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CommunityMembershipPendingEvent extends Event
{
    public const NAME = 'community_membership_pending';

    protected $community;
    protected $user;

    public function __construct(Community $community, User $user)
    {
        $this->community = $community;
        $this->user = $user;
    }

    public function getCommunity()
    {
        return $this->community;
    }

    public function getUser()
    {
        return $this->user;
    }
}
