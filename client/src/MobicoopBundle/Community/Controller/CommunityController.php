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
    public function show($id, CommunityManager $communityManager, Request $request, UserManager $userManager)
    {
        //Variable who indicate if user is part of community
        $isMember = false;
        // All community users ID
        $communityUsersId = [];
        // All community users
        $users = [];
        // Last three community users
        $lastUsers = [];

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
               
        //test if the community has members
        if (count($community->getCommunityUsers()) > 0) {
            foreach ($community->getCommunityUsers() as $communityUser) {
                // get all community users ID
                array_push($communityUsersId, $communityUser->getUser()->getId());
                // get all community Users
                array_push($users, $communityUser->getUser());
            }
        }
        $lastUsers = $communityManager->getLastUsers($id);
        dump($lastUsers);
       
       
        //test if the user logged is member of the community
        if (!is_null($user) && $user !=='' && in_array($user->getId(), $communityUsersId)) {
            $isMember = true;
        }
        
        return $this->render('@Mobicoop/community/showCommunity.html.twig', [
            'community' => $community,
            'user' => $user,
            'isMember' => $isMember,
            'searchRoute' => "covoiturage/recherche",
            'users' => $users,
            'lastUsers' => $lastUsers
        ]);
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
        $usersCommunity = array();

        //test if the community has members
        if (count($community->getCommunityUsers()) > 0) {
            foreach ($community->getCommunityUsers() as $userInCommunity) {
                $usersCommunity = [$userInCommunity->getUser()->getId()];
            }
        }
        //test if the user logged is member of the community
        if (!is_null($user) && $user !=='' && !in_array($user->getId(), $usersCommunity)) {
            $communityUser = new CommunityUser();
//        $this->denyAccessUnlessGranted('show', $community);
            $communityUser->setCommunity($community);
            $communityUser->setUser($user);
            $communityUser->setCreatedDate(new \DateTime());
            $communityUser->setStatus(0);

            $data=$communityManager->joinCommunity($communityUser);
            $reponseofmanager= $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
        }

        return $this->redirectToRoute('community_show', ['id' => $id]);
    }
}
