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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

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
    public function carpoolAdPost(int $communityId=null, ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager)
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

        // get the communities available for the user
        $communities = $communityManager->getAvailableUserCommunities($poster)->getMember();
        
        //get user's community
        if (!is_null($communityId)) {
            $community = $communityManager->getCommunity($communityId);
        }

        if ($request->query->get('origin')) {
            $initOrigin = new Address();
            $initOrigin->setDisplayLabel($request->query->get('origin'));
            $initOrigin->setLatitude($request->query->get('originLat'));
            $initOrigin->setLongitude($request->query->get('originLon'));
            $initOrigin->setAddressLocality($request->query->get('originAddressLocality'));
        }
        if ($request->query->get('destination')) {
            $initDestination = new Address();
            $initDestination->setDisplayLabel($request->query->get('destination'));
            $initDestination->setLatitude($request->query->get('destinationLat'));
            $initDestination->setLongitude($request->query->get('destinationLon'));
            $initDestination->setAddressLocality($request->query->get('destinationAddressLocality'));
        }
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communities'=>$communities,
                'community'=>(isset($community))?$community:null,
                'initOrigin'=>(isset($initOrigin)) ? $initOrigin : null,
                'initDestination'=>(isset($initDestination)) ? $initDestination : null,
                'initRegular'=>(is_null($request->query->get('regular')) || $request->query->get('regular')==="1") ? true : false,
                'initDate'=>($request->query->get('date')) ? $request->query->get('date') : null,
                'initTime'=>($request->query->get('time')) ? $request->query->get('time') : null,
            ]
        );
    }

    /**
     * Simple search results.
     * (POST)
     */
    public function carpoolSearchResult(Request $request)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'origin' => $request->request->get('origin'),
            'destination' => $request->request->get('destination'),
            'date' =>  $request->request->get('date'),
            'time' =>  $request->request->get('time'),
            'regular' => $request->request->get('regular')
        ]);
    }

    /**
     * Matching Search
     * AJAX
     */
    public function carpoolSearchMatching(Request $request, ProposalManager $proposalManager)
    {
        $origin_latitude = $request->query->get('origin_latitude');
        $origin_longitude = $request->query->get('origin_longitude');
        $destination_latitude = $request->query->get('destination_latitude');
        $destination_longitude = $request->query->get('destination_longitude');
        $date = \Datetime::createFromFormat("Y-m-d", $request->query->get('date'));
        $time = \Datetime::createFromFormat("H:i", $request->query->get('time'));
        $frequency = $request->query->get('regular')=="true" ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL;
        $regularLifeTime = $request->query->get('regularLifeTime');
        $strictDate = $request->query->get('strictDate');
        $useTime = $request->query->get('useTime');
        $strictPunctual = $request->query->get('strictPunctual');
        $strictRegular = $request->query->get('strictRegular');
        $role = $request->query->get('role', Criteria::ROLE_BOTH);

        // we have to merge matching proposals that concern both driver and passenger into a single matching
        $matchings = [];
        $proposalResult = null;

        // we post to the special collection /proposals/search, that will return only one virtual proposal (with its matchings)
        if ($proposalResults = $proposalManager->getMatchingsForSearch(
            $origin_latitude,
            $origin_longitude,
            $destination_latitude,
            $destination_longitude,
            $date,
            $frequency,
            $regularLifeTime,
            $strictDate,
            $useTime,
            $strictPunctual,
            $strictRegular,
            $role
        )) {
            if (is_array($proposalResults->getMember()) && count($proposalResults->getMember()) == 1) {
                $proposalResult = $proposalResults->getMember()[0];
            }
        }
        if ($proposalResult) {
            foreach ($proposalResult->getMatchingOffers() as $offer) {
                $matchings[$offer->getProposalRequest()->getId()] = $offer;
            }
            foreach ($proposalResult->getMatchingRequests() as $request) {
                if (!array_key_exists($request->getProposalOffer()->getId(), $matchings)) {
                    $matchings[$request->getProposalOffer()->getId()] = $request;
                }
            }
        }

        return $this->json($matchings);
    }

    /**
     * Initiate contact from carpool results
     */
    public function carpoolContact(Request $request, ProposalManager $proposalManager, UserManager $userManager)
    {
        // The matched proposal
        $matchedProposal = $proposalManager->getProposal($request->query->get('proposalId'));

        $data = [
            "proposalId" => (int)$request->query->get('proposalId'),
            "origin"=>[
                "latitude" => (float)$request->query->get('origin_latitude'),
                "longitude" => (float)$request->query->get('origin_longitude'),
                "streetAddress" => $request->query->get('origin_streetAddress'),
                "addressLocality" => $request->query->get('origin_addressLocality')
            ],
            "destination"=>[
                "latitude" => (float)$request->query->get('destination_latitude'),
                "longitude" => (float)$request->query->get('destination_longitude'),
                "streetAddress" => $request->query->get('destination_streetAddress'),
                "addressLocality" => $request->query->get('destination_addressLocality')
            ],
            "waypoints"=>[],
            "outwardDate" => Datetime::createFromFormat("Y-m-d\TH:i:s.u\Z", $request->query->get('date'))->format("Y-m-d"),
            "outwardTime" => Datetime::createFromFormat("Y-m-d\TH:i:s.u\Z", $request->query->get('date'))->format("H:i"),
            "seats" => 1,
            "price" => (float)$matchedProposal->getCriteria()->getPriceKm(),
            "regular" => $request->query->get('regular')==="false" ? false : true
        ];

        if ((bool)$request->query->get('driver') && (bool)$request->query->get('passenger')) {
            $data["driver"] = true;
            $data["passenger"] = true;
        } elseif ((bool)$request->query->get('driver')) {
            $data["driver"] = false;
            $data["passenger"] = true;
        } else {
            $data["driver"] = true;
            $data["passenger"] = false;
        }

        $proposal = $proposalManager->createProposalFromResult($data, $userManager->getLoggedUser());
        if ($proposal!==null) {
            return $this->json("ok");
        } else {
            return $this->json("error");
        }
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
