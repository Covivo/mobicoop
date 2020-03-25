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

namespace App\Solidary\EventSubscriber;

use App\Solidary\Event\SolidaryUserCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Solidary\Event\SolidaryUserStructureAccepted;
use App\Solidary\Event\SolidaryUserStructureRefused;
use App\Solidary\Event\SolidaryUserUpdated;
use App\Solidary\Service\SolidaryEventManager;

class SolidaryUserStructureSubscriber implements EventSubscriberInterface
{
    private $solidaryEventManager;

    public function __construct(SolidaryEventManager $solidaryEventManager)
    {
        $this->solidaryEventManager = $solidaryEventManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            SolidaryUserStructureAccepted::NAME => 'onSolidaryUserStructureAccepted',
            SolidaryUserStructureRefused::NAME => 'onSolidaryUserStructureRefused',
            SolidaryUserCreated::NAME => 'onSolidaryUserCreated',
            SolidaryUserUpdated::NAME => 'onSolidaryUserUpdated'
        ];
    }

    public function onSolidaryUserStructureAccepted(SolidaryUserStructureAccepted $event)
    {
        $this->solidaryEventManager->handleEvent(SolidaryUserStructureAccepted::NAME, $event);
    }

    public function onSolidaryUserStructureRefused(SolidaryUserStructureRefused $event)
    {
        $this->solidaryEventManager->handleEvent(SolidaryUserStructureRefused::NAME, $event);
    }

    public function onSolidaryUserCreated(SolidaryUserCreated $event)
    {
        $this->solidaryEventManager->handleEvent(SolidaryUserCreated::NAME, $event);
    }

    public function onSolidaryUserUpdated(SolidaryUserUpdated $event)
    {
        $this->solidaryEventManager->handleEvent(SolidaryUserUpdated::NAME, $event);
    }
}
