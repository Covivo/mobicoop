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
    public function eventList(EventManager $eventManager)
    {

        // We get all the events
        $eventComing = $eventManager->getEvents();
        $eventPassed = $eventManager->getEvents(0);

        if ($eventComing !== null) {
            $pointsComing = [];
            foreach ($eventComing as $event) {
                $pointsComing[] = [
                    "title"=>$event->getName().', '.$event->getAddress()->getAddressLocality(),
                    "latLng"=>["lat"=>$event->getAddress()->getLatitude(),"lon"=>$event->getAddress()->getLongitude()],
                    "event" => $event
                ];
            }
        }

        return $this->render('@Mobicoop/event/events.html.twig', [
            'eventComing' => $eventComing,
            'eventPassed' => $eventPassed,
            'pointComing' => $pointsComing
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
        $this->denyAccessUnlessGranted('create', $event);
        $user = $userManager->getLoggedUser();

        if ($request->isMethod('POST')) {
            // Create event and return response code
            if ($event = $eventManager->createEvent($request->request, $event, $user)) {
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

    /**
     * Show a event
     */
    public function eventShow($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {

        // retreive event;
        $event = $eventManager->getEvent($id);
        //$this->denyAccessUnlessGranted('show', $community);
        // retreive logged user

        $user = $userManager->getLoggedUser();
        return $this->render('@Mobicoop/event/event.html.twig', [
            'event' => $event,
            'user' => $user,
            'destination' => $event->getAddress(),
            'searchRoute' => "covoiturage/recherche",
            'error' => (isset($error)) ? $error : false
        ]);
    }

    /**
     * Get all proposals of an event
     *
     * @param integer $id
     * @param EventManager $eventManager
     * @return void
     */
    public function eventProposals(int $id, EventManager $eventManager)
    {
        $proposals = $eventManager->getProposals($id);
        $points = [];
        if ($proposals!==null) {
            foreach ($proposals as $proposal) {
                foreach ($proposal["waypoints"] as $waypoint) {
                    $points[] = [
                        "title"=>$waypoint["address"]["displayLabel"],
                        "latLng"=>["lat"=>$waypoint["address"]["latitude"],"lon"=>$waypoint["address"]["longitude"]]
                    ];
                }
            }
        }
        return new Response(json_encode($points));
    }
}
