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

namespace Mobicoop\Bundle\MobicoopBundle\RelayPoint\Controller;

use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Service\RelayPointManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for relay point related actions.
 */
class RelayPointController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * Relay point list.
     */
    public function relayPointList(RelayPointManager $relayPointManager)
    {
        return $this->render('@Mobicoop/relaypoint/relaypoints.html.twig', [
            // 'relayPoints' => $relayPointManager->getRelayPoints(),
            'searchRoute' => "covoiturage/recherche",
            ]);
    }

    /**
     * Get all relay points (AJAX).
     */
    public function getRelayPointList(RelayPointManager $relayPointManager, Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($relayPointManager->getRelayPoints());
        }
    }
}
