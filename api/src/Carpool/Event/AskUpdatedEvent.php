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
 */

namespace App\Carpool\Event;

use App\Carpool\Entity\Ask;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a new ask is updated.
 */
class AskUpdatedEvent extends Event
{
    public const NAME = 'carpool_ask_updated';

    protected $ask;

    public function __construct(Ask $ask)
    {
        $this->ask = $ask;
    }

    public function getAsk()
    {
        return $this->ask;
    }
}
