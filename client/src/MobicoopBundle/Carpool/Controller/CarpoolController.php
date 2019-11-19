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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
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
    public function carpoolAdPost(AdManager $adManager, UserManager $userManager, Request $request)
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
            //return $this->json(['result'=>$proposalManager->createProposalFromAd($data, $poster)]);
            return $this->json(['result'=>$adManager->createAd($data, $poster)]);
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
     * Ad results page.
     * (POST)
     */
    public function carpoolAdResults($id, ProposalManager $proposalManager)
    {
        $proposal = $proposalManager->getProposal($id);
        $this->denyAccessUnlessGranted('results', $proposal);

        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'proposalId' => $id
        ]);
    }

    /**
     * Ad result detail data.
     * (AJAX)
     */
    public function carpoolAdDetail($id, ProposalManager $proposalManager, AdManager $adManager)
    {
        $proposal = $proposalManager->getProposal($id);
        $this->denyAccessUnlessGranted('results', $proposal);
        if ($results = $adManager->getAd($id)) {
            return $this->json($results->getResults());
        }
        return $this->json([]);
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
    public function carpoolSearchMatching(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);
        if ($params['date'] && $params['date'] != '') {
            $date = \Datetime::createFromFormat("Y-m-d", $params['date']);
        } else {
            $date = new \DateTime();
        }
        //$time = \Datetime::createFromFormat("H:i", $request->query->get('time'));
        $frequency = isset($params['regular']) ? ($params['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL) : Criteria::FREQUENCY_PUNCTUAL;
        $strictDate = isset($params['strictDate']) ? $params['strictDate'] : null;
        $strictPunctual = isset($params['strictPunctual']) ? $params['strictPunctual'] : null;
        $strictRegular = isset($params['strictRegular']) ? $params['strictRegular'] : null;
        $role = isset($params['role']) ? $params['role'] : Criteria::ROLE_BOTH;
        $userId = isset($params['userId']) ? $params['userId'] : null;
        $communityId = isset($params['communityId']) ? $params['communityId'] : null;

        $result = [];
        if ($ad = $adManager->getResultsForSearch(
            $params['origin'],
            $params['destination'],
            $date,
            null,
            $frequency,
            $strictDate,
            $strictPunctual,
            $strictRegular,
            $role,
            $userId,
            $communityId
        )) {
            $result = $ad->getResults();
        }

        return $this->json($result);
    }

    /**
     * Initiate contact from carpool results
     * (AJAX POST)
     */
    public function carpoolContact(Request $request, ProposalManager $proposalManager, UserManager $userManager)
    {
        $params = json_decode($request->getContent(), true);

        // if the matching set, it means the contact is made after an ad matching
        if (isset($params['matchingId'])) {
            // create the ask and return the result
            return $this->json("ok");
        }
        
        if (!is_null($proposalManager->createProposalFromSearch($userManager->getLoggedUser(), $params))) {
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

        // if the matching is set, it means the ask is made after an ad matching
        if (isset($params['matchingId'])) {
            // create the ask and return the result
            return $this->json("ok");
        }
                
        if (!is_null($proposalManager->createProposalFromSearch($userManager->getLoggedUser(), $params, true))) {
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
