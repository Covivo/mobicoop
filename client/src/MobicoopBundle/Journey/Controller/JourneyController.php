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

namespace Mobicoop\Bundle\MobicoopBundle\Journey\Controller;

use Mobicoop\Bundle\MobicoopBundle\Journey\Service\JourneyManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for journeys related actions.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class JourneyController extends AbstractController
{
    private $journeyManager;

    public function __construct(JourneyManager $journeyManager)
    {
        $this->journeyManager = $journeyManager;
    }

    public function cities()
    {
        return $this->render('@Mobicoop/journey/cities.html.twig', [
            'cities' => $this->journeyManager->getCities()
        ]);
    }

    public function fromCity(string $origin)
    {
        $journeys = $this->journeyManager->getDestinations($origin);
        return $this->render('@Mobicoop/journey/results.html.twig', [
            "type"=>"origin",
            "journeys"=>$journeys['journeys'],
            "origin"=>$journeys['origin'],
            "originSanitize"=>$origin
        ]);
    }

    public function toCity(string $destination)
    {
        $journeys = $this->journeyManager->getOrigins($destination);
        return $this->render('@Mobicoop/journey/results.html.twig', [
            "type"=>"destination",
            "journeys"=>$journeys['journeys'],
            "destination"=>$journeys['destination'],
            "destinationSanitize"=>$destination
        ]);
    }

    public function fromCityToCity(string $origin, string $destination, int $frequency=1, int $page=1, int $perPage=300, UserManager $userManager)
    {
        $user = $userManager->getLoggedUser();

        $journeys = $this->journeyManager->getFromTo($origin, $destination, $frequency, $page, $perPage);
        return $this->render('@Mobicoop/journey/result.html.twig', [
            "journeys"=>$journeys['journeys'],
            "origin"=>$journeys['origin'],
            "originSanitize"=>$origin,
            "destination"=>$journeys['destination'],
            "destinationSanitize"=>$destination,
            "total"=>$journeys['total'],
            "frequency"=>$frequency,
            "page"=>$page,
            "perPage"=>$perPage,
            "logged" => ($user instanceof User) ? true : false
        ]);
    }

    /**
     * Get the popular journeys
     */
    public function popularJourneys(Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($this->journeyManager->getPopularJourneys());
        }
        return new JsonResponse("Bad request");
    }

    /**
     * Get the popular journeys
     */
    public function popularJourneysHome(Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse($this->journeyManager->getPopularJourneys(true));
        }
        return new JsonResponse("Bad request");
    }

    /**
     * Create a search Ad from a Proposal
     *
     * @param integer $proposalId   The base Proposal
     * @return int|null The Ad's id created
     */
    public function createSearchFromProposal(int $proposalId)
    {
        $journey = $this->journeyManager->createSearchFromProposal($proposalId);
        return new JsonResponse(['proposalId'=>$journey->getProposalId()]);
    }
}
