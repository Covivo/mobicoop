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
use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    use HydraControllerTrait;

    private $midPrice;
    private $highPrice;
    private $forbiddenPrice;
    private $defaultRole;

    public function __construct($midPrice, $highPrice, $forbiddenPrice, $defaultRole)
    {
        $this->midPrice = $midPrice;
        $this->highPrice = $highPrice;
        $this->forbiddenPrice = $forbiddenPrice;
        $this->defaultRole = $defaultRole;
    }
    
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
                $data['userId'] = $data['userDelegated'];
                $data['posterId'] = $poster->getId();
            } else {
                $this->denyAccessUnlessGranted('post', $proposal);
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

        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            "pricesRange" => [
                "mid" => $this->midPrice,
                "high" => $this->highPrice,
                "forbidden" => $this->forbiddenPrice
            ]
        ]);
    }

    /**
     * Create the first carpooling ad.
     */
    public function carpoolFirstAdPost()
    {
        $proposal = new Proposal();
        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render('@Mobicoop/carpool/publish.html.twig', [
            "pricesRange" => [
                "mid" => $this->midPrice,
                "high" => $this->highPrice,
                "forbidden" => $this->forbiddenPrice
            ]
        ]);
    }
        
    /**
    * Create a solidary exclusive carpooling ad.
    */
    public function carpoolSolidaryExclusiveAdPost()
    {
        $proposal = new Proposal();
        $this->denyAccessUnlessGranted('create_ad', $proposal);
        return $this->render(
            '@Mobicoop/carpool/publish.html.twig',
            [
                'solidaryExclusiveAd'=>true,
                "pricesRange" => [
                    "mid" => $this->midPrice,
                    "high" => $this->highPrice,
                    "forbidden" => $this->forbiddenPrice
                ]
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
                'regular'=>json_decode($request->request->get('regular')),
                'date'=>$request->request->get('date'),
                'time'=>$request->request->get('time'),
                "pricesRange" => [
                    "mid" => $this->midPrice,
                    "high" => $this->highPrice,
                    "forbidden" => $this->forbiddenPrice
                ]
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
            'proposalId' => $id
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
            'user' => $userManager->getLoggedUser()
        ]);
    }

    /**
     * Matching Search
     * (AJAX POST)
     */
    public function carpoolSearchMatching(Request $request, AdManager $adManager, UserManager $userManager)
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
            //We get the id of proposal the current user already asks (no matter the status)
            if ($userManager->getLoggedUser() != null) {
                $proposalAlreadyAsk = $userManager->getAsks($userManager->getLoggedUser());
                foreach ($result as $key => $oneResult) {
                    $result[$key]['alreadyask'] = 0;
                    if ($oneResult['resultPassenger'] != null) {
                        $proposal = $oneResult['resultPassenger']['outward']['proposalId'];
                        if (in_array($proposal, $proposalAlreadyAsk['offers'])) {
                            $result[$key]['alreadyask'] = 1;
                        }
                    }
                    if ($oneResult['resultDriver'] != null) {
                        $proposal = $oneResult['resultDriver']['outward']['proposalId'];
                        if (in_array($proposal, $proposalAlreadyAsk['request'])) {
                            $result[$key]['alreadyask'] = 1;
                        }
                    }
                }
            }
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
