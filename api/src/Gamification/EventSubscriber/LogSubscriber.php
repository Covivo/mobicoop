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

use App\Action\Event\LogEvent;
use App\Gamification\Service\GamificationManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class LogSubscriber implements EventSubscriberInterface
{
    private $gamificationManager;
    private $gamificationActive;

    public function __construct(GamificationManager $gamificationManager, bool $gamificationActive)
    {
        $this->gamificationManager = $gamificationManager;
        $this->gamificationActive = $gamificationActive;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LogEvent::NAME => 'onLogEvent'
        ];
    }

    public function onLogEvent(LogEvent $event)
    {
        if ($this->gamificationActive && $event->getLog()->getUser()->hasGamification()) {
            $this->gamificationManager->handleLog($event->getLog());
        }
    }
}
