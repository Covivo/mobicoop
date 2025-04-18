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

namespace Mobicoop\Bundle\MobicoopBundle\Event\Controller;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Communication\Entity\Report;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller class for events related actions.
 */
class EventController extends AbstractController
{
    use HydraControllerTrait;

    private $router;
    private $mandatoryDescription;
    private $mandatoryFullDescription;
    private $mandatoryImage;
    private $defaultNbEventsPerPage;
    private $eventAssociatedToCommunity;
    private $eventManager;
    private $eventWidget;

    public function __construct(UrlGeneratorInterface $router, bool $mandatoryDescription, bool $mandatoryFullDescription, bool $mandatoryImage, int $defaultNbEventsPerPage, array $eventAssociatedToCommunity, EventManager $eventManager, bool $eventWidget)
    {
        $this->router = $router;
        $this->mandatoryDescription = $mandatoryDescription;
        $this->mandatoryFullDescription = $mandatoryFullDescription;
        $this->mandatoryImage = $mandatoryImage;
        $this->defaultNbEventsPerPage = $defaultNbEventsPerPage;
        $this->eventAssociatedToCommunity = $eventAssociatedToCommunity;
        $this->eventManager = $eventManager;
        $this->eventWidget = $eventWidget;
    }

    /**
     * Events list controller.
     *
     * @param mixed $tabDefault
     */
    public function eventList($tabDefault)
    {
        $tab = 'tab-current';
        if ('evenements-passes' == $tabDefault) {
            $tab = 'tab-passed';
        }

        return $this->render('@Mobicoop/event/events.html.twig', [
            'defaultItemsPerPage' => $this->defaultNbEventsPerPage,
            'tabDefault' => $tab,
            'canSelectCommunity' => $this->eventAssociatedToCommunity['activated'],
        ]);
    }

    /**
     * Get all events.
     */
    public function getEventList(EventManager $eventManager, Request $request)
    {
        // We get all the events
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $search = (isset($data['search']) && !is_null($data['search'])) ? $data['search'] : [];
            if (!$data['coming']) {
                $search = (isset($data['searchPassed']) && !is_null($data['searchPassed'])) ? $data['searchPassed'] : [];
            }

            $apiEvents = $eventManager->getEvents($data['coming'], null, 'fromDate', 'asc', $data['perPage'], $data['page'], $search, $data['communityId']);
            $events = $apiEvents->getMember();
            $eventsTotalItems = $apiEvents->getTotalItems();
            $pointsComing = [];
            if (null !== $events && $data['coming']) {
                foreach ($events as $event) {
                    $pointsComing[] = [
                        'title' => $event->getName().', '.$event->getAddress()->getAddressLocality(),
                        'latLng' => ['lat' => $event->getAddress()->getLatitude(), 'lon' => $event->getAddress()->getLongitude()],
                        'event' => $event,
                    ];
                }
            }

            return new JsonResponse([
                'eventComing' => ($data['coming']) ? $events : null,
                'eventPassed' => (!$data['coming']) ? $events : null,
                'points' => $pointsComing,
                'totalItems' => $eventsTotalItems,
            ]);
        }
    }

    /**
     * Get last events created.
     */
    public function getLastEventCreated(EventManager $eventManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $apiEvents = $eventManager->getLastEventsCreated('createdDate', 'desc', $data['perPage'], $data['page']);
            $events = $apiEvents->getMember();

            return new JsonResponse([
                'eventComing' => ($data['coming']) ? $events : null,
            ]);
        }
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
            if (!$user instanceof User) {
                $user = null;

                return new Response(json_encode('error.loggedOut'));
            }
            // Create event and return response code
            if ($event = $eventManager->createEvent($request->request, $event, $user)) {
                // Post avatar of the event
                if ($this->mandatoryImage || null != $request->files->get('avatar')) {
                    $image = new Image();
                    $image->setEventFile($request->files->get('avatar'));
                    $image->setEventId($event->getId());
                    $image->setName($event->getName());
                    if ($image = $imageManager->createImage($image)) {
                        return new Response();
                    }
                    // If an error occur on upload image, the event is already create, so we delete him
                    $eventManager->deleteEvent($event->getId());

                    // return error if image post didnt't work
                    return new Response(json_encode('error.image'));
                }

                return new Response();
            }

            // return error if event post didn't work
            return new Response(json_encode('error.event.create'));
        }

        // Redirect to user_login
        if (!$user instanceof User) {
            var_dump('not logged');
            $user = null;

            return $this->redirectToRoute('user_login');
        }

        return $this->render('@Mobicoop/event/createEvent.html.twig', [
            'mandatoryDescription' => $this->mandatoryDescription,
            'mandatoryFullDescription' => $this->mandatoryFullDescription,
            'mandatoryImage' => $this->mandatoryImage,
            'canSelectCommunity' => $this->eventAssociatedToCommunity['activated'],
            'mandatoryCommunity' => $this->eventAssociatedToCommunity['mandatory'],
        ]);
    }

    /**
     * Show a event.
     *
     * @param mixed $id
     */
    public function eventShow($id, EventManager $eventManager, UserManager $userManager)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);
        $this->denyAccessUnlessGranted('show', $event);

        // get event's proposals
        $ads = $eventManager->getAds($id);
        $ways = [];
        if (count($ads) > 0) {
            foreach ($ads as $ad) {
                $origin = null;
                $destination = null;
                $isRegular = null;
                $date = null;

                if (Ad::FREQUENCY_REGULAR === $ad['frequency']) {
                    $isRegular = true;
                } else {
                    $date = new \DateTime($ad['outwardDate']);
                    $date = $date->format('Y-m-d');
                }
                $currentAd = [
                    'frequency' => (Ad::FREQUENCY_PUNCTUAL == $ad['frequency']) ? 'punctual' : 'regular',
                    'carpoolerFirstName' => $ad['user']['givenName'],
                    'carpoolerLastName' => $ad['user']['shortFamilyName'],
                    'waypoints' => [],
                ];
                foreach ($ad['outwardWaypoints'] as $waypoint) {
                    if (0 === $waypoint['position']) {
                        $origin = $waypoint['address'];
                    } elseif ($waypoint['destination']) {
                        $destination = $waypoint['address'];
                    }
                    $currentAd['waypoints'][] = [
                        'title' => $waypoint['address']['addressLocality'],
                        'destination' => $waypoint['destination'],
                        'latLng' => ['lat' => $waypoint['address']['latitude'], 'lon' => $waypoint['address']['longitude']],
                    ];
                }
                $searchLinkParams = [
                    'origin' => json_encode($origin),
                    'destination' => json_encode($destination),
                    'regular' => $isRegular,
                    'date' => $date,
                    'eid' => $event->getId(),
                ];
                $currentAd['searchLink'] = $this->router->generate('carpool_search_result_get', $searchLinkParams, UrlGeneratorInterface::ABSOLUTE_URL);
                $ways[] = $currentAd;
            }
        }
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/event/event.html.twig', [
            'event' => $event,
            'user' => $user,
            'destination' => $event->getAddress(),
            'searchRoute' => 'covoiturage/recherche',
            'points' => $ways,
            'eventWidget' => $this->eventWidget,
        ]);
    }

    public function eventEdit($id, EventManager $eventManager, ImageManager $imageManager, Request $request)
    {
        $event = $eventManager->getEvent($id);
        $this->denyAccessUnlessGranted('update', $event);

        if ($request->isMethod('POST')) {
            if ($event = $eventManager->updateEvent($request->request, $event)) {
                if (null != $request->files->get('avatar')) {
                    if (count($event->getImages()) > 0) {
                        $imageManager->deleteImage($event->getImages()[0]->getId());
                    }
                    $image = new Image();
                    $image->setEventFile($request->files->get('avatar'));
                    $image->setEventId($event->getId());
                    $image->setName($event->getName());
                    if ($image = $imageManager->createImage($image)) {
                        return new Response();
                    }

                    return new Response(json_encode('error.image'));
                }

                return new Response();
            }

            // return error if event post didn't work
            return new Response(json_encode('error.event.create'));
        }

        return $this->render('@Mobicoop/event/updateEvent.html.twig', [
            'event' => $event,
            'mandatoryDescription' => $this->mandatoryDescription,
            'mandatoryFullDescription' => $this->mandatoryFullDescription,
            'mandatoryImage' => $this->mandatoryImage,
            'canSelectCommunity' => $this->eventAssociatedToCommunity['activated'],
            'mandatoryCommunity' => $this->eventAssociatedToCommunity['mandatory'],
        ]);
    }

    /**
     * Show a event widget.
     *
     * @param mixed $id
     */
    public function eventWidget($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);

        // $this->denyAccessUnlessGranted('show', $community);
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/event/event-widget.html.twig', [
            'event' => $event,
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche',
        ]);
    }

    /**
     * Show an event widget page to get the widget.
     *
     * @param mixed $id
     */
    public function eventGetWidget($id, EventManager $eventManager, UserManager $userManager, Request $request)
    {
        // retreive event;
        $event = $eventManager->getEvent($id);

        // $this->denyAccessUnlessGranted('show', $community);
        return $this->render('@Mobicoop/event/event-get-widget.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Report an event.
     *
     * @param mixed $id
     */
    public function eventReport($id, EventManager $eventManager, DataProvider $dataProvider, Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $success = false;

            // name is the honey pot field
            if (isset($data['name']) && '' !== $data['name']) {
                $success = true;
            }

            // Post the Report
            if (
                isset($data['email'], $data['text'], $data['name'])
                && '' !== $data['email'] && '' !== $data['text'] && '' == $data['name']
            ) {
                $dataProvider->setClass(Report::class);

                $report = new Report();
                $report->setEventId($id);
                $report->setReporterEmail($data['email']);
                $report->setText($data['text']);

                $response = $dataProvider->post($report);

                if (201 === $response->getCode()) {
                    $success = true;
                }
            }
        }

        return new JsonResponse(['success' => $success]);
    }

    /**
     * Delete an avent.
     *
     * @return JsonResponse
     */
    public function eventDelete(Request $request)
    {
        if ($request->isMethod('DELETE')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['eventId'])) {
                return new JsonResponse([
                    'message' => 'error',
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->json($this->eventManager->deleteEvent($data['eventId']));
        }
    }
}
