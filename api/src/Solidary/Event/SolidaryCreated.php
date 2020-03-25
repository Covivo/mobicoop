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

namespace App\Solidary\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a solidary is created
 */
class SolidaryCreated extends Event
{
    public const NAME = 'solidary_create';

    // Just an object because sometimes it's a User, sometimes it's a Solidary
    // We check it in SolidaryEventManager->handleEvent()
    protected $object;

    public function __construct(Object $object)
    {
        $this->object = $object;
    }

    public function getObject()
    {
        return $this->object;
    }
}
