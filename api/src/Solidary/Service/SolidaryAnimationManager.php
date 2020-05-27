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

namespace App\Solidary\Service;

use App\Solidary\Entity\SolidaryAnimation;
use App\Solidary\Event\SolidaryAnimationPostedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAnimationManager
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function treatSolidaryAnimation(SolidaryAnimation $solidaryAnimation)
    {
        $event = new SolidaryAnimationPostedEvent($solidaryAnimation);
        $this->dispatcher->dispatch(SolidaryAnimationPostedEvent::NAME, $event);
    }
}
