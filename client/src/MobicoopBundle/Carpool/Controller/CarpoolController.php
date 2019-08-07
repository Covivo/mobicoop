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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\AdForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Service\ExternalJourneyManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    /**
     * Create a carpooling ad.
     */


    public function ad(AdManager $adManager, UserManager $userManager, Request $request, CommunityManager $communityManager, CommunityController $communityController)
    {
        //get price from the client/.env file
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../../../.env');
        $priceCarpool = $_ENV['PRICE_CARPOOL'];
        $ad = new Ad();
        $this->denyAccessUnlessGranted('post', $ad);
        $ad->setRole(Ad::ROLE_BOTH);
        $ad->setType(Ad::TYPE_ONE_WAY);
        $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
//        $ad->setFrequency(Ad::FREQUENCY_REGULAR);
        $ad->setPrice($priceCarpool);
        $ad->setUser($userManager->getLoggedUser());

        $form = $this->createForm(AdForm::class, $ad, ['csrf_protection' => false]);
        $error = false;
        $success = false;
        $idCommunity ='';

        if (count($_GET) > 0 && array_key_exists('id', $_GET) && !is_null($_GET['id']) && $_GET['id'] !='') {
            $idCommunity = $_GET['id'];
        }
        //        ajout de la gestion des communautés
        $hydraCommunities = $communityManager->getCommunities();
//        dump($hydraCommunities);
        $communities =[];
        if ($hydraCommunities && count($hydraCommunities->getMember())>0) {
            foreach ($hydraCommunities->getMember() as $value) {
                foreach (array($value) as $community) {
                    if ($community->isSecured(true)) {
//                        dump($community->getCommunityUsers());
                        $membersOfCommunity = array();
                        foreach ($community->getCommunityUsers() as $user) {
                            $membersOfCommunity = [$user->getUser()->getId()];
                        }
                        $logged = $userManager->getLoggedUser();
                        $isLogged = boolval($logged); // cast to boolean
                        // don't display the secured community if the user is not logged or if the user doesn't belong to the secured community
                        if (!$isLogged || !in_array($logged->getId(), $membersOfCommunity)) {
                            continue;
                        }
                    }

//                    $communities[$community->getId()] = $community->getName();
                    $communityToTab = (object)["id"=> $community->getId(), "communityName"=> $community->getName()];
                    $communities[]=$communityToTab;
                }
            }
        }
        if ($request->isMethod('POST')) {
            $createToken = $request->request->get('createToken');
            if (!$this->isCsrfTokenValid('ad-create', $createToken)) {
                return new Response('Broken Token CSRF ', 403);
            }
            $form->submit($request->request->get($form->getName()));
            // $form->submit($request->request->all());

//            test if a community is filled
            if ($ad->getCommunity() !== '' && !is_null($ad->getCommunity())) {
                $communityController->joinCommunity($ad->getCommunity(), $communityManager, $userManager);
            }
        }

        // If it's a get, just render the form !
        if (!$form->isSubmitted()) {
            return $this->render('@Mobicoop/ad/create.html.twig', [
                'form' => $form->createView(),
                'error' => $error,
                'communities' => $communities,
                'idCommunity' => $idCommunity
            ]);
        }

        // Not Valid populate error
        if (!$form->isValid()) {
            $error = [];
            // Fields
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    foreach ($child->getErrors(true) as $err) {
                        $error[$child->getName()][] = $err->getMessage();
                    }
                }
            }
            return $this->json(['error' => $error, 'success' => $success]);
        }

        // Error happen durring proposal creation
        try {
            $proposal = $adManager->createProposalFromAd($ad);
            $success = true;
        } catch (Error $err) {
            $error = $err;
        }
        $proposalSuccess = $success ? $proposal->getId() : false;

        return $this->json(['error' => $error, 'success' => $success, 'proposal' => $proposalSuccess]);
    }

    /**
     * Simple search results.
     */
    public function simpleSearchResults($origin, $destination, $origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date, ProposalManager $proposalManager)
    {
        return $this->render('@Mobicoop/search/simple_results.html.twig', [
            'origin' => urldecode($origin),
            'destination' => urldecode($destination),
            'origin_latitude' => urldecode($origin_latitude),
            'origin_longitude' => urldecode($origin_longitude),
            'destination_latitude' => urldecode($destination_latitude),
            'destination_longitude' => urldecode($destination_longitude),
            'date' =>  \Datetime::createFromFormat("YmdHis", $date),
            'hydra' => $proposalManager->getMatchingsForSearch($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, \Datetime::createFromFormat("YmdHis", $date)),
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
