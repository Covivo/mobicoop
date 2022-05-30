<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Image\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Community\Entity\Community;
use App\Event\Entity\Event;
use App\Image\Entity\Image;
use App\Image\Service\ImageManager;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Entity\RelayPointType;
use App\User\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DeleteSubscriber implements EventSubscriberInterface
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['deleteVersions', EventPriorities::PRE_WRITE],
        ];
    }

    public function deleteVersions(ViewEvent $event)
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // needs to be modified for each new related entity (event, user etc...)
        if ((!($object instanceof Image) && !($object instanceof Event) && !($object instanceof Community) && !($object instanceof User) && !($object instanceof RelayPoint) && !($object instanceof RelayPointType)) || Request::METHOD_DELETE !== $method) {
            return;
        }
        if ($object instanceof Image) {
            // deletion of Image => we delete the versions
            $this->imageManager->deleteVersions($object);
        } elseif ($object instanceof Event || $object instanceof Community || $object instanceof User || $object instanceof RelayPoint || $object instanceof RelayPointType) {
            // deletion of Event => we delete the versions of all related images
            foreach ($object->getImages() as $image) {
                $this->imageManager->deleteVersions($image);
            }
        }
    }
}
