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

namespace App\Action\EventSubscriber;

use App\Action\Event\AnimationMadeEvent;
use App\Action\Service\DiaryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for Animation events
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AnimationSubscriber implements EventSubscriberInterface
{
    private $diaryManager;

    public function __construct(DiaryManager $diaryManager)
    {
        $this->diaryManager = $diaryManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AnimationMadeEvent::NAME => 'onAnimationMade'
        ];
    }

    public function onAnimationMade(AnimationMadeEvent $event)
    {
        $this->diaryManager->handleAnimation($event->getAnimation());
    }
}
