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
use Mobicoop\Bundle\MobicoopBundle\Community\Controller\CommunityController;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
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
    public function post(int $communityId, ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager)
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
        // todo : add a csrf token

        // get the communities available for the user
        $communities = $communityManager->getAvailableUserCommunities($poster)->getMember();
        
        //get user's community
        $community = $communityManager->getCommunity($communityId);

        if ($request->query->get('origin')) {
            $initOrigin = new Address();
            $initOrigin->setDisplayLabel($request->query->get('origin'));
            $initOrigin->setLatitude($request->query->get('originLat'));
            $initOrigin->setLongitude($request->query->get('originLon'));
        }
        if ($request->query->get('destination')) {
            $initDestination = new Address();
            $initDestination->setDisplayLabel($request->query->get('destination'));
            $initDestination->setLatitude($request->query->get('destinationLat'));
            $initDestination->setLongitude($request->query->get('destinationLon'));
         }
        if ($request->query->get('destination')) {
            $initDestination = new Address();
            $initDestination->setDisplayLabel($request->query->get('destination'));
            $initDestination->setLatitude($request->query->get('destinationLat'));
            $initDestination->setLongitude($request->query->get('destinationLon'));
        }
 
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communities'=>$communities,
                'community'=>$community,
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
     */
    public function simpleSearchResults($origin, $destination, $origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date, ProposalManager $proposalManager)
    {
        // $offers= $proposalManager->getMatchingsForSearch($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, \Datetime::createFromFormat("YmdHis", $date));
        // $reponseofmanager= $this->handleManagerReturnValue($offers);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        return $this->render('@Mobicoop/search/simple_results.html.twig', [
            'origin' => urldecode($origin),
            'destination' => urldecode($destination),
            'origin_latitude' => urldecode($origin_latitude),
            'origin_longitude' => urldecode($origin_longitude),
            'destination_latitude' => urldecode($destination_latitude),
            'destination_longitude' => urldecode($destination_longitude),
            'date' =>  $date,
            // 'hydra' => $offers,
            'MatchingSearchUrl' => "/matching/search"
        ]);
    }

    /**
     * Matching Search
     */
    public function MatchingSearch(Request $request, ProposalManager $proposalManager)
    {
        $origin_latitude = $request->query->get('origin_latitude');
        $origin_longitude = $request->query->get('origin_longitude');
        $destination_latitude = $request->query->get('destination_latitude');
        $destination_longitude = $request->query->get('destination_longitude');
        $date = Datetime::createFromFormat("Y-m-d\TH:i:s\Z", $request->query->get('date'));

        return $this->json($proposalManager->getMatchingsForSearch(
            $origin_latitude,
            $origin_longitude,
            $destination_latitude,
            $destination_longitude,
            $date,
            DataProvider::RETURN_JSON
        ));
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
    /**
     * Results of a carpooling ad.
     */
    public function postResults($id, ProposalManager $proposalManager)
    {
        $proposal = $proposalManager->getProposal($id);
        $reponseofmanager= $this->handleManagerReturnValue($proposal);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $this->denyAccessUnlessGranted('results', $proposal);
        return $this->render('@Mobicoop/proposal/ad_results.html.twig', [
            'proposal' => $proposal
        ]);
    }
}
