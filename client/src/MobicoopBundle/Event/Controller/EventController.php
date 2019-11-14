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

namespace Mobicoop\Bundle\MobicoopBundle\Event\Controller;

use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Event\Service\EventManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Controller class for events related actions.
 *
 */
class EventController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * Get all events.
     */
    public function eventList(EventManager $eventManager, UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();

        if ($user) {
            // We get all the communities
            $events = $eventManager->getEvents(new \DateTime());
        }

        return $this->render('@Mobicoop/event/events.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * Create an event
     */
    public function eventCreate(
        EventManager $eventManager,
        UserManager $userManager,
        Request $request,
        ImageManager $imageManager
    ) {
        $event = new Event();
        //$this->denyAccessUnlessGranted('create', $event);
        $user = new User($userManager->getLoggedUser()->getId());

        if ($request->isMethod('POST')) {
            // Create event and return response code
            if ( $event = $eventManager->createEvent($request->request, $event, $user)) {
                // Post avatar of the event
                $image = new Image();
                $image->setEventFile($request->files->get('avatar'));
                $image->setEventId($event->getId());
                $image->setName($event->getName());
                if ($image = $imageManager->createImage($image)) {
                    return new Response();
                }
                // return error if image post didnt't work
                return new Response(json_encode('error.image'));
            }
            // return error if event post didn't work
            return new Response(json_encode('error.event.create'));
        }
        return $this->render('@Mobicoop/event/createEvent.html.twig', [
        ]);
    }
}
