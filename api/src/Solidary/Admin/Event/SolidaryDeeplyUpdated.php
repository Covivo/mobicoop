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

namespace App\Solidary\Admin\Event;

use App\Solidary\Entity\Solidary;
use Symfony\Contracts\EventDispatcher\Event;
use App\User\Entity\User;

/**
 * Event sent when a solidary is deeply updated => replaced by a new solidary
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryDeeplyUpdated extends Event
{
    public const NAME = 'solidary_deeply_updated';

    protected $poster;
    protected $solidary;

    public function __construct(User $poster, Solidary $solidary)
    {
        $this->poster = $poster;
        $this->solidary = $solidary;
    }

    public function getPoster()
    {
        return $this->poster;
    }

    public function getSolidary()
    {
        return $this->solidary;
    }
}
