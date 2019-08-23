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

    public function post(ProposalManager $proposalManager, UserManager $userManager, Request $request, CommunityManager $communityManager, CommunityController $communityController)
    {
        $proposal = new Proposal();
        $this->denyAccessUnlessGranted('create_ad', $proposal);

        if ($request->isMethod('POST')) {
            $this->denyAccessUnlessGranted('post', $proposal);
            $data = json_decode($request->getContent(), true);
            $result = $proposalManager->createProposalFromAd($data);                
            return $this->json(['result'=>$result]);
        }

        return $this->render('@Mobicoop/carpool/publish.html.twig');


        // $ad->setRole(Ad::ROLE_BOTH);
        // $ad->setType(Ad::TYPE_ONE_WAY);
        // $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
//        $ad->setFrequency(Ad::FREQUENCY_REGULAR);
        // $ad->setPrice($priceCarpool);
        // $ad->setUser($userManager->getLoggedUser());

        //        ajout de la gestion des communautés
//         $hydraCommunities = $communityManager->getCommunities();
//         $communities =[];
//         if ($hydraCommunities && count($hydraCommunities->getMember())>0) {
//             foreach ($hydraCommunities->getMember() as $value) {
//                 foreach (array($value) as $community) {
//                     if ($community->isSecured(true)) {
//                         $membersOfCommunity = array();
//                         foreach ($community->getCommunityUsers() as $user) {
//                             $membersOfCommunity = [$user->getUser()->getId()];
//                         }
//                         $logged = $userManager->getLoggedUser();
//                         $reponseofmanager= $this->handleManagerReturnValue($logged);
//                         if (!empty($reponseofmanager)) {
//                             return $reponseofmanager;
//                         }
//                         $isLogged = boolval($logged); // cast to boolean
//                         // don't display the secured community if the user is not logged or if the user doesn't belong to the secured community
//                         if (!$isLogged || !in_array($logged->getId(), $membersOfCommunity)) {
//                             continue;
//                         }
//                     }

// //                    $communities[$community->getId()] = $community->getName();
//                     $communityToTab = (object)["id"=> $community->getId(), "communityName"=> $community->getName()];
//                     $communities[]=$communityToTab;
//                 }
//             }
//         }
        //if ($request->isMethod('POST')) {
            // if ($ad->getCommunity() !== '' && !is_null($ad->getCommunity())) {
            //     $communityController->joinCommunity($ad->getCommunity(), $communityManager, $userManager);
            // }

        //}

        //    return $this->render('@Mobicoop/carpool/publish.html.twig', [
                // 'communities' => $communities,
                // 'idCommunity' => $idCommunity
        //    ]);

        // Error happen durring proposal creation
        // try {
        //     $proposal = $adManager->createProposalFromAd($ad);
        //     $reponseofmanager= $this->handleManagerReturnValue($proposal);
        //     if (!empty($reponseofmanager)) {
        //         return $reponseofmanager;
        //     }
        //     $success = true;
        // } catch (Error $err) {
        //     $error = $err;
        //     $success= false;
        // }
        // $proposalSuccess = $success ? $proposal->getId() : false;

        // return $this->json(['error' => $error, 'success' => $success, 'proposal' => $proposalSuccess]);
    }

    /**
     * Simple search results.
     */
    public function simpleSearchResults($origin, $destination, $origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date, ProposalManager $proposalManager)
    {
        $offers= $proposalManager->getMatchingsForSearch($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, \Datetime::createFromFormat("YmdHis", $date));
        $reponseofmanager= $this->handleManagerReturnValue($offers);
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
            'date' =>  \Datetime::createFromFormat("YmdHis", $date),
            'hydra' => $offers,
        ]);
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
     * Ad post results.
     */
    public function adPostResults($id, ProposalManager $proposalManager)
    {
        $proposal = $proposalManager->getProposal($id);
        $reponseofmanager= $this->handleManagerReturnValue($proposal);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }

        $this->denyAccessUnlessGranted('results', $proposal);

        // foreach ($proposal->getMatchingOffers() as $matching) {
        //     if ($matching->getProposalOffer() instanceof Proposal) {
        //         if (!$matching->getProposalOffer()->getUser() instanceof User) {
        //             $proposalOffer = $proposalManager->getProposal($matching->getProposalOffer()->getId());
        //             $matching->getProposalOffer()->setUser($proposalOffer->getUser());
        //         }
        //     }
        //     if ($matching->getProposalRequest() instanceof Proposal) {
        //         if (!$matching->getProposalRequest()->getUser() instanceof User) {
        //             $proposalRequest = $proposalManager->getProposal($matching->getProposalRequest()->getId());
        //             $matching->getProposalRequest()->setUser($proposalRequest->getUser());
        //         }
        //     }
        // }
        // foreach ($proposal->getMatchingRequests() as $matching) {
        //     if ($matching->getProposalOffer() instanceof Proposal) {
        //         if (!$matching->getProposalOffer()->getUser() instanceof User) {
        //             $proposalOffer = $proposalManager->getProposal($matching->getProposalOffer()->getId());
        //             $matching->getProposalOffer()->setUser($proposalOffer->getUser());
        //         }
        //     }
        //     if ($matching->getProposalRequest() instanceof Proposal) {
        //         if (!$matching->getProposalRequest()->getUser() instanceof User) {
        //             $proposalRequest = $proposalManager->getProposal($matching->getProposalRequest()->getId());
        //             $matching->getProposalRequest()->setUser($proposalRequest->getUser());
        //         }
        //     }
        // }

        return $this->render('@Mobicoop/proposal/ad_results.html.twig', [
            'proposal' => $proposal
        ]);
    }
}
