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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Controller;

use DateTime;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * Create a carpooling ad.
     */
    public function carpoolAdPost(ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager)
    {
        $proposal = new Proposal();
        $poster = $userManager->getLoggedUser();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($poster && isset($data['userDelegated']) && $data['userDelegated'] != $poster->getId()) {
                $this->denyAccessUnlessGranted('post_delegate', $proposal);
            } else {
                $this->denyAccessUnlessGranted('post', $proposal);
            }
            return $this->json(['result'=>$proposalManager->createProposalFromAd($data, $poster)]);
        }

        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render('@Mobicoop/carpool/publish.html.twig');
    }

    /**
     * Create the first carpooling ad.
     */
    public function carpoolFirstAdPost(ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager)
    {
        $proposal = new Proposal();
        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render('@Mobicoop/carpool/publish.html.twig');
    }
        
    /**
    * Create a solidary carpooling ad.
    */
    public function carpoolSolidaryAdPost(ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager)
    {
        $proposal = new Proposal();
        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'solidaryAd'=>true,
            ]
        );
    }

    /**
     * Create a carpooling ad from a search component (home, community...)
     * (POST)
     */
    public function carpoolAdPostFromSearch(Request $request)
    {
        $proposal = new Proposal();

        $this->denyAccessUnlessGranted('create_ad', $proposal);
        
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communityIds'=>$request->request->get('communityId') ? [(int)$request->request->get('communityId')] : null,
                'origin'=>$request->request->get('origin'),
                'destination'=>$request->request->get('destination'),
                'regular'=>$request->request->get('regular'),
                'date'=>$request->request->get('date'),
                'time'=>$request->request->get('time')
            ]
        );
    }

    /**
     * Ad results.
     * (POST)
     */
    public function carpoolAdResult(Request $request)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'proposalId' => $request->request->get('proposalId')
        ]);
    }

    /**
     * Simple search results.
     * (POST)
     */
    public function carpoolSearchResult(Request $request, UserManager $userManager)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'origin' => $request->request->get('origin'),
            'destination' => $request->request->get('destination'),
            'date' =>  $request->request->get('date'),
            'time' =>  $request->request->get('time'),
            'regular' => $request->request->get('regular'),
            'communityId' => $request->request->get('communityId'),
            'user' => $userManager->getLoggedUser()
        ]);
    }

    /**
     * Matching Search
     * (AJAX POST)
     */
    public function carpoolSearchMatching(Request $request, ProposalManager $proposalManager)
    {
        $params = json_decode($request->getContent(), true);
        if ($params['date'] && $params['date'] != '') {
            $date = \Datetime::createFromFormat("Y-m-d", $params['date']);
        } else {
            $date = new \DateTime();
        }
        //$time = \Datetime::createFromFormat("H:i", $request->query->get('time'));
        $frequency = isset($params['regular']) ? ($params['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL) : Criteria::FREQUENCY_PUNCTUAL;
        $regularLifeTime = isset($params['regularLifeTime']) ? $params['regularLifeTime'] : null;
        $strictDate = isset($params['strictDate']) ? $params['strictDate'] : null;
        $useTime = isset($params['useTime']) ? $params['useTime'] : null;
        $strictPunctual = isset($params['strictPunctual']) ? $params['strictPunctual'] : null;
        $strictRegular = isset($params['strictRegular']) ? $params['strictRegular'] : null;
        $role = isset($params['role']) ? $params['role'] : Criteria::ROLE_BOTH;
        $userId = isset($params['userId']) ? $params['userId'] : null;
        $communityId = isset($params['communityId']) ? $params['communityId'] : null;

        $matchings = [];
        $proposalResult = null;

        // we post to the special collection /proposals/search, that will return only one virtual proposal (with its matchings)
        if ($proposalResults = $proposalManager->getMatchingsForSearch(
            $params['origin'],
            $params['destination'],
            $date,
            $frequency,
            $regularLifeTime,
            $strictDate,
            $useTime,
            $strictPunctual,
            $strictRegular,
            $role,
            $userId,
            $communityId
        )) {
            if (is_array($proposalResults->getMember()) && count($proposalResults->getMember()) == 1) {
                $proposalResult = $proposalResults->getMember()[0];
            }
        }
        if ($proposalResult) {
            $matchings = $proposalResult->getResults();
        }

        return $this->json($matchings);
    }

    /**
     * Initiate contact from carpool results
     * (AJAX POST)
     */
    public function carpoolContact(Request $request, ProposalManager $proposalManager, UserManager $userManager)
    {
        $params = json_decode($request->getContent(), true);

        // if the proposal search is set, it means the contact is made after an ad matching
        if (isset($params['proposalSearch'])) {
            // create the ask and return the result
        }
        
        if (!is_null($this->createProposalFromParams($proposalManager, $userManager->getLoggedUser(), $params))) {
            return $this->json("ok");
        } else {
            return $this->json("error");
        }
    }

    /**
     * Formal ask from carpool results
     * (AJAX POST)
     */
    public function carpoolAsk(Request $request, ProposalManager $proposalManager, UserManager $userManager)
    {
        $params = json_decode($request->getContent(), true);

        // if the proposal search is set, it means the ask is made after an ad matching
        if (isset($params['proposalSearch'])) {
            // create the ask and return the result
        }
                
        if (!is_null($this->createProposalFromParams($proposalManager, $userManager->getLoggedUser(), $params, true))) {
            return $this->json("ok");
        } else {
            return $this->json("error");
        }
    }

    /**
     * Create a proposal from params
     *
     * @param ProposalManager $proposalManager
     * @param User $user
     * @param array $params     The params
     * @param bool $formalAsk   True if we have to create a formal ask
     * @return void
     */
    private function createProposalFromParams(ProposalManager $proposalManager, User $user, array $params, bool $formalAsk=false)
    {
        $data = [
            "formalAsk" => $formalAsk,
            "private" => true,
            "proposalId" => $params['proposalId'],
            "origin"=>$params['origin'],
            "destination"=>$params['destination'],
            "outwardDate" => $params['date'] ? DateTime::createFromFormat(DateTime::ISO8601, $params['date'])->format('Y-m-d') : (new \Datetime())->format('Y-m-d'),
            "outwardTime" => $params['time'] ? DateTime::createFromFormat(DateTime::ISO8601, $params['time'])->format('H:i') : null,
            "seats" => isset($params['seats']) ? $params['seats'] : 1,
            "driver" => $params['driver'],
            "passenger" => $params['passenger'],
            "priceKm" => $params['priceKm'],
            "originalPrice" => isset($params['originalPrice']) ? $params['originalPrice'] : null,
            "computedPrice" => isset($params['computedPrice']) ? $params['computedPrice'] : null,
            "regular" => $params['regular'],
            "waypoints" => []
        ];
        return $proposalManager->createProposalFromAd($data, $user->getLoggedUser());
    }


    /**
     * Provider rdex
     */
    public function rdexProvider(ExternalJourneyManager $externalJourneyManager)
    {
        return $this->json($externalJourneyManager->getExternalJourneyProviders(DataProvider::RETURN_JSON));
    }

    /**
     * Journey rdex
     */
    public function rdexJourney(ExternalJourneyManager $externalJourneyManager, Request $request)
    {
        $params = [
            'provider' => $request->query->get('provider'),
            'driver' => $request->query->get('driver'),
            'passenger' => $request->query->get('passenger'),
            'from_latitude' => $request->query->get('from_latitude'),
            'from_longitude' => $request->query->get('from_longitude'),
            'to_latitude' => $request->query->get('to_latitude'),
            'to_longitude' => $request->query->get('to_longitude')
        ];
        return $this->json($externalJourneyManager->getExternalJourney($params, DataProvider::RETURN_JSON));
    }
}
