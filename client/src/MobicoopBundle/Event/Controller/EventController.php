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

use GuzzleHttp\RequestOptions;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Event\Service\EventManager;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\Image\Service\ImageManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for events related actions.
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

        if (null !== $eventComing) {
            $pointsComing = [];
            foreach ($eventComing as $event) {
                $pointsComing[] = [
                    'title' => $event->getName().', '.$event->getAddress()->getAddressLocality(),
                    'latLng' => ['lat' => $event->getAddress()->getLatitude(), 'lon' => $event->getAddress()->getLongitude()],
                    'event' => $event,
                ];
            }
        }

        return $this->render('@Mobicoop/event/events.html.twig', [
            'eventComing' => $eventComing,
            'eventPassed' => $eventPassed,
            'pointComing' => $pointsComing,
        ]);
    }

    /**
     * Create an event.
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
                //If an error occur on upload image, the event is already create, so we delete him
                $eventManager->deleteEvent($event->getId());
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
     * Show a event.
     */
    public function eventShow($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);
        $this->denyAccessUnlessGranted('show', $event);

        // get event's proposals
        $proposals = $eventManager->getProposals($id);
        $points = [];

        if (null !== $proposals) {
            foreach ($proposals as $proposal) {
                foreach ($proposal['waypoints'] as $waypoint) {
                    $points[] = [
                        'title' => $waypoint['address']['displayLabel'],
                        'latLng' => ['lat' => $waypoint['address']['latitude'], 'lon' => $waypoint['address']['longitude']],
                    ];
                }
            }
        }
        
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/event/event.html.twig', [
            'event' => $event,
            'user' => $user,
            'destination' => $event->getAddress(),
            'searchRoute' => 'covoiturage/recherche',
            'error' => (isset($error)) ? $error : false,
            'points' => $points,
        ]);
    }

    /**
     * Get all proposals of an event.
     *
     * @return Response
     */
    public function eventProposals(int $id, EventManager $eventManager)
    {
        $proposals = $eventManager->getProposals($id);
        $points = [];
        if (null !== $proposals) {
            foreach ($proposals as $proposal) {
                foreach ($proposal['waypoints'] as $waypoint) {
                    $points[] = [
                        'title' => $waypoint['address']['displayLabel'],
                        'latLng' => ['lat' => $waypoint['address']['latitude'], 'lon' => $waypoint['address']['longitude']],
                    ];
                }
            }
        }

        return new Response(json_encode($points));
    }

    /**
     * Show a widget event.
     */
    public function eventWidget($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);

        //$this->denyAccessUnlessGranted('show', $community);
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/event/event-widget.html.twig', [
            'event' => $event,
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche',
            'error' => (isset($error)) ? $error : false,
        ]);
    }

    /**
     * Show a widget event.
     */
    public function eventGetWidget($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);
        //$this->denyAccessUnlessGranted('show', $community);
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/event/event-get-widget.html.twig', [
            'event' => $event,
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche',
            'error' => (isset($error)) ? $error : false,
        ]);
    }

    /**
     * Report an event.
     */
    public function eventReport($id, EventManager $eventManager, DataProvider $dataProvider, Request $request)
    {
        $success = false;

        // RETRIEVE EVENT
        $event = $eventManager->getEvent($id);
        $this->denyAccessUnlessGranted('report', $event);

        // SEND MAIL
        if ($request->request->has('email') && $request->request->has('description')) {
            $response = $dataProvider->simplePost('events/' . $id . '/report', [
                'email' => $request->request->get('email'),
                'description' => $request->request->get('description')
            ]);

            if (200 === $response->getCode()) {
                $success = true;
            }
        }

        return new JsonResponse(['success' => $success]);
    }
}
