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
 **************************/

namespace App\Event\EventListener;

use App\Event\Entity\Event;
use App\Event\Service\EventManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Event Event listener
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class EventLoadListener
{
    private $eventManager;
    private $avatarDefault;

    public function __construct(EventManager $eventManager, string $avatarDefault)
    {
        $this->eventManager = $eventManager;
        $this->avatarDefault = $avatarDefault;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $event = $args->getEntity();

        if ($event instanceof Event) {
            $event->setUrlKey($this->eventManager->generateUrlKey($event));

            // Check if http or https is given in url
            // If not, we use https by default
            $parsedUrl = parse_url($event->getUrl());
            if (!isset($parsedUrl['scheme'])) {
                $event->setUrl("https://".$event->getUrl());
            }

            // default avatar
            $event->setDefaultAvatar($this->avatarDefault);
        }
    }
}
