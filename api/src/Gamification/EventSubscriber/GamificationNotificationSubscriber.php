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

namespace App\Gamification\EventSubscriber;

use App\Gamification\Event\BadgeEarnedEvent;
use App\Gamification\Event\RewardStepEarnedEvent;
use App\Gamification\Service\GamificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Gamification : Listen to all event that need to be return at the end of the request (earn a Badge, validate a step...)
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GamificationNotificationSubscriber implements EventSubscriberInterface
{
    private $gamificationManager;

    public function __construct(GamificationManager $gamificationManager)
    {
        $this->gamificationManager = $gamificationManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            RewardStepEarnedEvent::NAME => 'onRewardStepEarnedEvent',
            BadgeEarnedEvent::NAME => 'onBadgeEarnedEvent',
        ];
    }

    public function onRewardStepEarnedEvent(RewardStepEarnedEvent $event)
    {
        $this->gamificationManager->handleGamificationNotification($event->getRewardStep());
    }

    public function onBadgeEarnedEvent(BadgeEarnedEvent $event)
    {
        $this->gamificationManager->handleGamificationNotification($event->getBadge());
    }
}
