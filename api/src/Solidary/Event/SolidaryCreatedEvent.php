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
 */

namespace App\Solidary\Event;

use App\App\Entity\App;
use App\Solidary\Entity\Solidary;
use App\User\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event sent when a solidary is created.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryCreatedEvent extends Event
{
    public const NAME = 'solidary_create';

    protected $user;
    protected $author;

    public function __construct(User $user, $author, $solidary)
    {
        $this->user = $user;
        $this->author = $author;
        $this->solidary = $solidary;
        // If it's an App, it means that this User registered himself from the front
        if ($author instanceof App) {
            $this->author = $user;
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getSolidary()
    {
        return $this->solidary;
    }
}
