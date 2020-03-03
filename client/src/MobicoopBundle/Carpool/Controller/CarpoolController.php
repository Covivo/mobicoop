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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Symfony\Component\HttpFoundation\Response;

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
    public function carpoolAdPost(AdManager $adManager, UserManager $userManager, Request $request)
    {
        $ad = new Ad();
        $poster = $userManager->getLoggedUser();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($poster && isset($data['userDelegated']) && $data['userDelegated'] != $poster->getId()) {
                $this->denyAccessUnlessGranted('post_delegate', $ad);
                $data['userId'] = $data['userDelegated'];
                $data['posterId'] = $poster->getId();
            } else {
                $this->denyAccessUnlessGranted('post', $ad);
                $data['userId'] = $poster->getId();
            }
            if (!isset($data['outwardDate']) || $data['outwardDate'] == '') {
                $data['outwardDate'] = new \DateTime();
            } else {
                $data['outwardDate'] = \DateTime::createFromFormat('Y-m-d', $data['outwardDate']);
            }
            if (isset($data['returnDate']) && $data['returnDate'] != '') {
                $data['returnDate'] = \DateTime::createFromFormat('Y-m-d', $data['returnDate']);
                $data['oneway'] = true; // only for punctual journey
            } else {
                $data['oneway'] = false; // only for punctual journey
            }

            // one-way for regular
            if ($data['regular']) {
                $data['oneway'] = true;
                foreach ($data['schedules'] as $schedule) {
                    if (isset($schedule['returnTime']) && !is_null($schedule['returnTime'])) {
                        $data['oneway'] = false;
                    }
                }
            }

            return $this->json(['result'=>$adManager->createAd($data)]);
        }

        $this->denyAccessUnlessGranted('create_ad', $ad);
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
     * Create the first carpooling ad.
     */
    public function carpoolFirstAdPost()
    {
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);
        
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
        $ad = new Ad();
        $this->denyAccessUnlessGranted('create_ad', $ad);
        
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'communityIds'=>$request->request->get('communityId') ? [(int)$request->request->get('communityId')] : null,
                'origin'=>$request->request->get('origin'),
                'destination'=>$request->request->get('destination'),
                'regular'=>$request->request->get('communityId') ? json_decode($request->request->get('regular')) : $this->defaultRegular,
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
     * @param ProposalManager $proposalManager
     * @param Request $request
     * @return JsonResponse
     */
    public function carpoolAdDelete(ProposalManager $proposalManager, Request $request, UserManager $userManager)
    {
        if ($request->isMethod('DELETE')) {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['proposalId'])) {
                return new JsonResponse([
                    'message' => 'error'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $proposal = $proposalManager->getProposal($data['proposalId']);
            
            $this->denyAccessUnlessGranted('delete_ad', $proposal);
            // add the id of the deleter
            $data['deleterId'] = $userManager->getLoggedUser()->getId();
            if ($response = $proposalManager->deleteProposal($data['proposalId'], $data)) {
                return new JsonResponse(
                    ["message" => "delete.success"],
                    \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED
                );
            }
            return new JsonResponse(
                ["message" => "delete.error"],
                \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        }

        return new JsonResponse(
            ["message" => "delete.error"],
            \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Ad results.
     * (POST)
     */
    public function carpoolAdResults($id, AdManager $adManager)
    {
        $ad = $adManager->getAd($id);
        $this->denyAccessUnlessGranted('results_ad', $ad);

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
     * PausedAd
     * (AJAX POST)
     */
    public function pauseAd(Request $request, AdManager $adManager, UserManager $userManager)
    {
        if ($request->isMethod('PUT')) {
            $data = json_decode($request->getContent(), true);
            $ad = new Ad();
            $ad->setId($data['proposalId']);
            $ad->setProposalId($data['proposalId']);
            $ad->setPaused($data['paused']);
            if ($return = $adManager->updateAd($ad)) {
                return new JsonResponse(
                    ["message" => "success"],
                    \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED
                );
            }
            return new JsonResponse(
                ["message" => "error"],
                \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        }
        return new JsonResponse(
            ["message" => "error"],
            \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
        );
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
