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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Security\AdVoter;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolController extends AbstractController
{
    use HydraControllerTrait;

    private $midPrice;
    private $highPrice;
    private $forbiddenPrice;
    private $defaultRole;
    private $defaultRegular;
    private $platformName;
    private $carpoolRDEXJourneys;

    public function __construct($midPrice, $highPrice, $forbiddenPrice, $defaultRole, bool $defaultRegular, string $platformName, bool $carpoolRDEXJourneys)
    {
        $this->midPrice = $midPrice;
        $this->highPrice = $highPrice;
        $this->forbiddenPrice = $forbiddenPrice;
        $this->defaultRole = $defaultRole;
        $this->defaultRegular = $defaultRegular;
        $this->platformName = $platformName;
        $this->carpoolRDEXJourneys = $carpoolRDEXJourneys;
    }
    
    /**
     * Create a carpooling ad.
     */
    public function carpoolAdPost(AdManager $adManager, Request $request)
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return $this->json(['result'=>$adManager->createAd($data)]);
        }
        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            "pricesRange" => [
                "mid" => $this->midPrice,
                "high" => $this->highPrice,
                "forbidden" => $this->forbiddenPrice
            ],
            "regular" => $this->defaultRegular
        ]);
    }

    /**
     * Update a carpooling ad.
     */
    public function carpoolAdUpdate(int $id, AdManager $adManager, Request $request)
    {
        $ad = $adManager->getFullAd($id);
        $this->denyAccessUnlessGranted('update_ad', $ad);

        $hasAsks = false;
        $hasPotentialAds = false;
        if ($ad->getPotentialCarpoolers() > 0) {
            $hasPotentialAds = true;
        }
        if (count($ad->getAsks()) > 0) {
            $hasAsks = true;
        }

        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);
            $data["mailSearchLink"] = $this->generateUrl("carpool_search_result_get", [], UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->json(['result'=>$adManager->updateAd($data, $ad)]);
        }

        return $this->render('@Mobicoop/carpool/update.html.twig', [
            "ad" => $ad,
            "hasAsks" => $hasAsks,
            "hasPotentialAds" => $hasPotentialAds
        ]);
    }

    /**
     * Create the first carpooling ad.
     */
    public function carpoolFirstAdPost()
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_first_ad', $ad);
        
        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            "firstAd" => true,
            "pricesRange" => [
                "mid" => $this->midPrice,
                "high" => $this->highPrice,
                "forbidden" => $this->forbiddenPrice,
            ],
            "regular" => $this->defaultRegular
        ]);
    }
        
    /**
    * Create a solidary exclusive carpooling ad.
    */
    public function carpoolSolidaryExclusiveAdPost()
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);

        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'solidaryExclusiveAd'=>true,
                "pricesRange" => [
                    "mid" => $this->midPrice,
                    "high" => $this->highPrice,
                    "forbidden" => $this->forbiddenPrice
                ],
                "regular" => $this->defaultRegular
            ]
        );
    }

    /**
     * Create a carpooling ad from a search component (home, community...)
     * (POST)
     */
    public function carpoolAdPostFromSearch(Request $request)
    {
//        $ad = new Ad();
//        $this->denyAccessUnlessGranted('create_ad', $ad);
        
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communityIds'=>$request->request->get('communityId') ? [(int)$request->request->get('communityId')] : null,
                'origin'=>$request->request->get('origin'),
                'destination'=>$request->request->get('destination'),
                'regular'=>$request->request->get('regular') ? json_decode($request->request->get('regular')) : $this->defaultRegular,
                'date'=>$request->request->get('date'),
                'time'=>$request->request->get('time'),
                "pricesRange" => [
                    "mid" => $this->midPrice,
                    "high" => $this->highPrice,
                    "forbidden" => $this->forbiddenPrice
                ],
            ]
        );
    }

    /**
     * Delete a carpooling ad.
     * @param AdManager $adManager
     * @param Request $request
     * @param UserManager $userManager
     * @return JsonResponse
     */
    public function carpoolAdDelete(AdManager $adManager, Request $request, UserManager $userManager)
    {
        if ($request->isMethod('DELETE')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['adId'])) {
                return new JsonResponse([
                    'message' => 'error'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            // add the id of the deleter
            $data['deleterId'] = $userManager->getLoggedUser()->getId();

            return $this->json($response = $adManager->deleteAd($data['adId'], $data));
        }
    }

    /**
     * Ad results.
     * (POST)
     */
    public function carpoolAdResults($id)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'proposalId' => $id,
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => false, // No RDEX, this not a new search
            'defaultRole'=>$this->defaultRole
        ]);
    }

    /**
     * Ad results after authentication.
     * (POST)
     */
    public function carpoolAdResultsAfterAuthentication($id, AdManager $adManager)
    {
        // first we need to clone the source proposal, as it should be anonymous
        $adManager->claimAd($id);
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'proposalId' => $id,
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => false, // No RDEX, this not a new search
            'defaultRole'=>$this->defaultRole
        ]);
    }

    /**
     * Ad result detail data.
     * (AJAX)
     */
    public function carpoolAdDetail($id, AdManager $adManager, Request $request)
    {
        $filters = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (isset($data['filters'])) {
                $filters = $data['filters'];
            }
        }
        if ($ad = $adManager->getAd($id, $filters)) {
            //$this->denyAccessUnlessGranted('results_ad', $ad);
            return $this->json($ad->getResults());
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
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
            'defaultRole'=>$this->defaultRole
        ]);
    }

    /**
     * Simple search results (GET)
     *
     * @param Request $request          The request
     * @param UserManager $userManager  The userManager
     * @return Response|null            The response
     */
    public function carpoolSearchResultGet(Request $request, UserManager $userManager)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            // todo: use if we can keep the proposal (request or offer) if we delete the matched one - cf CarpoolSubscriber
//            'proposalId' => $request->get('pid'),
            'origin' => $request->get('origin'),
            'destination' => $request->get('destination'),
            'date' => $request->get('date'),
            'regular' => (bool) $request->get('regular'),
            'communityId' => $request->get('cid'),
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
            'defaultRole'=>$this->defaultRole
        ]);
    }

    /**
     * RDEX search results (public GET link)
     *
     * @param Request $request          The request
     * @param UserManager $userManager  The userManager
     * @param string $externalId        The external ID of the proposal that was generated for the external search
     * @return Response|null            The response
     */
    public function carpoolSearchResultFromRdexLink(Request $request, UserManager $userManager, string $externalId)
    {
        return $this->render('@Mobicoop/carpool/results.html.twig', [
            'externalId' => $externalId,
            'user' => $userManager->getLoggedUser(),
            'platformName' => $this->platformName,
            'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
            'defaultRole'=>$this->defaultRole
        ]);
    }

    /**
     * Community proposal search results (public GET link)
     * A proposal ID must be given, we need to check if the current user has the right on this community proposal,
     * then we create a new proposal with the same origin/destination than the given proposal.
     *
     * @param Request $request              The request
     * @param UserManager $userManager      The userManager
     * @param int $communityProposalId      The community proposal ID from which we want to make a search
     * @return Response|null                The response
     */
    public function carpoolSearchResultFromCommunityProposal(Request $request, UserManager $userManager, AdManager $adManager, int $communityProposalId)
    {
        // TODO : check the auth
        // TODO : get the original ad
        // $ad = $adManager->getAd($communityProposalId);
        // $origin = $ad->getOutwardWaypoints()->???;
        // $destination = $ad->getOutwardWaypoints()->???;
        // return $this->render('@Mobicoop/carpool/results.html.twig', [
        //     'origin' => $origin,
        //     'destination' => $destination,
        //     'date' => $request->get('date'),
        //     'regular' => (bool) $request->get('regular'),
        //     'communityId' => $request->get('cid'),
        //     'user' => $userManager->getLoggedUser(),
        //     'platformName' => $this->platformName,
        //     'externalRDEXJourneys' => $this->carpoolRDEXJourneys,
        //     'defaultRole'=>$this->defaultRole
        // ]);
    }

    /**
     * Matching Search
     * (AJAX POST)
     */
    public function carpoolSearchMatching(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);
        if (isset($params['date']) && $params['date'] != '') {
            $date = \Datetime::createFromFormat("Y-m-d", $params['date']);
        } else {
            $date = new \DateTime();
        }
        $time = null;
        if (isset($params['time']) && $params['time'] != '') {
            $time = \Datetime::createFromFormat("H:i", $params['time']);
        }
        $regular = isset($params['regular']) ? $params['regular'] : false;
        $strictDate = isset($params['strictDate']) ? $params['strictDate'] : null;
        $strictPunctual = isset($params['strictPunctual']) ? $params['strictPunctual'] : null;
        $strictRegular = isset($params['strictRegular']) ? $params['strictRegular'] : null;
        $role = isset($params['role']) ? $params['role'] : $this->defaultRole;
        $userId = isset($params['userId']) ? $params['userId'] : null;
        $communityId = isset($params['communityId']) ? $params['communityId'] : null;

        $filters = isset($params['filters']) ? $params['filters'] : null;

        $result = [];
        if ($ad = $adManager->getResultsForSearch(
            $params['origin'],
            $params['destination'],
            $date,
            $time,
            $regular,
            $strictDate,
            $strictPunctual,
            $strictRegular,
            $role,
            $userId,
            $communityId,
            $filters
        )) {
            $result = $ad->getResults();
        }

        return $this->json($result);
    }

    /**
     * Initiate contact from carpool results
     * (AJAX POST)
     */
    public function carpoolContact(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);

        if (!is_null($adManager->createAsk($params))) {
            return $this->json("ok");
        } else {
            return $this->json("error");
        }
    }

    /**
     * Formal ask from carpool results
     * (AJAX POST)
     */
    public function carpoolAsk(Request $request, AdManager $adManager)
    {
        $params = json_decode($request->getContent(), true);
                
        if (!is_null($adManager->createAsk($params, true))) {
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
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            return $this->json($externalJourneyManager->getExternalJourney($data, DataProvider::RETURN_JSON));
        }

        return $this->json("");
    }
}
