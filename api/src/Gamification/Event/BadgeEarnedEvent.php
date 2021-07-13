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

namespace App\Gamification\Event;

use App\Gamification\Entity\Reward;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Gamification : Event sent when a Badge is earned
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgeEarnedEvent extends Event
{
    public const NAME = 'badge_earned';

    protected $reward;

    public function __construct(Reward $reward)
    {
        $this->reward = $reward;
    }

    public function getReward()
    {
        return $this->reward;
    }
}
