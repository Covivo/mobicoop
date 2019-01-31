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
 **************************/

namespace App\Image\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Image\Entity\Image;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Image\Service\ImageManager;

final class DeleteSubscriber implements EventSubscriberInterface
{
    private $imageManager;
    
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['deleteThumbnails', EventPriorities::POST_WRITE],
        ];
    }
    
    public function deleteThumbnails(GetResponseForControllerResultEvent $event)
    {
        $image = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        
        if (!$image instanceof Image || Request::METHOD_DELETE !== $method) {
            return;
        }
        $this->imageManager->deleteVersions($image);
    }
}