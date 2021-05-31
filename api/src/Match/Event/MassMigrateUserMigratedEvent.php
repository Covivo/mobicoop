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

namespace App\Match\Event;

use App\Match\Entity\MassPerson;
use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a massUser is migrated to a regular User
 */
class MassMigrateUserMigratedEvent extends Event
{
    public const NAME = 'mass_migrate_user_migrated';

    protected $user;

    public function __construct(MassPerson $massPerson)
    {
        $this->massPerson = $massPerson;
    }

    public function getMassPerson()
    {
        return $this->massPerson;
    }
}
