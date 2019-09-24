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

namespace Mobicoop\Bundle\MobicoopBundle\Community\Controller;

use Mobicoop\Bundle\MobicoopBundle\Community\Form\CommunityUserForm;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\CommunityUser;
use Mobicoop\Bundle\MobicoopBundle\Community\Form\CommunityForm;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for community related actions.
 *
 */
class CommunityController extends AbstractController
{
    use HydraControllerTrait;
    /**
     * Get all communities.
     */
    public function list(CommunityManager $communityManager)
    {
        $this->denyAccessUnlessGranted('list', new Community());
        return $this->render('@Mobicoop/community/communities.html.twig', [
            'communities' => $communityManager->getCommunities(),
        ]);
    }

    /**
     * Create a community
     */
    public function create(CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        $community = new Community();
        $this->denyAccessUnlessGranted('create', $community);
        $community->setUser($userManager->getLoggedUser());

        $form = $this->createForm(CommunityForm::class, $community);
        $error = false;
       
        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($community = $communityManager->createCommunity($community)) {
                return $this->redirectToRoute('community_list');
            }
            $error = true;
        }

        return $this->render('@Mobicoop/community/createCommunity.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * Show a community
     */
    public function show($id, CommunityManager $communityManager, UserManager $userManager)
    {
        // retrive community;
        $community = $communityManager->getCommunity($id);
        $reponseofmanager= $this->handleManagerReturnValue($community);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }

        // retrive logged user
        $this->denyAccessUnlessGranted('show', $community);
        $user = $userManager->getLoggedUser();
        
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        return $this->render('@Mobicoop/community/community.html.twig', [
            'community' => $community,
            'user' => $user,
            'searchRoute' => "covoiturage/recherche",
        ]);
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
     * @return void
     */
    public function communityUser(int $id, CommunityManager $communityManager, UserManager $userManager)
    {
        if ($userManager->getLoggedUser()) {
            $communityUser = $communityManager->getCommunityUser($id, $userManager->getLoggedUser()->getId());
            $reponseofmanager= $this->handleManagerReturnValue($communityUser);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
            return $this->json($communityUser);
        }
        
        return new Response;
    }


    /**
     * Join a community
     */
    public function joinCommunity($id, CommunityManager $communityManager, UserManager $userManager)
    {
        $community = $communityManager->getCommunity($id);
        $user = $userManager->getLoggedUser();
        $reponseofmanager= $this->handleManagerReturnValue($user);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $communityUsersId = [];
        foreach ($community->getCommunityUsers() as $communityUser) {
            // get all community users ID
            array_push($communityUsersId, $communityUser->getUser()->getId());
        }
        //test if the user logged is already a member of the community
        if ($user && $user !=='' && !in_array($user->getId(), $communityUsersId)) {
            $communityUser = new CommunityUser();
            
            $communityUser->setCommunity(new Community($id));
            $communityUser->setUser($user);
            $communityUser->setCreatedDate(new \DateTime());
            $communityUser->setStatus(0);

            $data=$communityManager->joinCommunity($communityUser);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }
        return new Response();
    }

    /**
     * Undocumented function
     *
     * @param [type] $id
     * @param CommunityManager $communityManager
     * @param UserManager $userManager
     * @return void
     */
    public function getCommunityLastUsers(int $id, CommunityManager $communityManager)
    {
        // get the last 3 users and formate them to be used with vue
        $lastUsers = $communityManager->getLastUsers($id);
        foreach ($lastUsers as $key => $commUser) {
            $lastUsersFormated[$key]["name"]=ucfirst($commUser->getUser()->getGivenName())." ".ucfirst($commUser->getUser()->getFamilyName());
            $lastUsersFormated[$key]["acceptedDate"]=$commUser->getAcceptedDate()->format('d/m/Y');
        }
        return new Response(json_encode($lastUsersFormated));
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function getCommunityMemberList(int $id, CommunityManager $communityManager)
    {
        // retrive community;
        $community = $communityManager->getCommunity($id);
        $reponseofmanager= $this->handleManagerReturnValue($community);
        if (!empty($reponseofmanager)) {
            return $reponseofmanager;
        }
        $users = [];
        //test if the community has members
        if (count($community->getCommunityUsers()) > 0) {
            foreach ($community->getCommunityUsers() as $communityUser) {
                if ($communityUser->getStatus() == 1) {
                    // get all community Users
                    array_push($users, $communityUser->getUser());
                }
            }
        }
        return new Response(json_encode($users));
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param CommunityManager $communityManager
     * @return void
     */
    public function getCommunityProposals(int $id, CommunityManager $communityManager)
    {
        $proposals = $communityManager->getProposals($id);
        $points = [];
        foreach($proposals as $proposal){
            foreach ($proposal["waypoints"] as $waypoint) {
                $points[] = [
                    "title"=>$waypoint["address"]["displayLabel"],
                    "latLng"=>["lat"=>$waypoint["address"]["latitude"],"lon"=>$waypoint["address"]["longitude"]]
                ];
            }
        }
        return new Response(json_encode($points));
    }

}
