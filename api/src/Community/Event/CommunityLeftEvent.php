<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Community\Event;

use App\Community\Entity\Community;
use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when an community is left by a user.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CommunityLeftEvent extends Event
{
    public const NAME = 'community_left';

    protected $_user;
    protected $_community;

    public function __construct(User $user, Community $community)
    {
        $this->_user = $user;
        $this->_community = $community;
    }

    public function getUser(): User
    {
        return $this->_user;
    }

    public function getCommunity(): Community
    {
        return $this->_community;
    }
}
