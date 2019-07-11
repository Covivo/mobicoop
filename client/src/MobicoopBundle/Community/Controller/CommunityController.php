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
    /**
     * Get all communities.
     */
    public function list(CommunityManager $communityManager)
    {
        $this->denyAccessUnlessGranted('list', new Community());
        return $this->render('@Mobicoop/community/communities.html.twig', [
            'hydra' => $communityManager->getCommunities(),
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
    public function show($id, CommunityManager $communityManager, Request $request)
    {
        $communityUser = new CommunityUser();
        $community = $communityManager->getCommunity($id);
        $this->denyAccessUnlessGranted('show', $community);

        $form = $this->createForm(CommunityUserForm::class, $communityUser);
        $error = false;

        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($communityUser = $communityManager->joinCommunity($communityUser)) {
                return $this->redirectToRoute('community_show', ['id' => $id]);
            }
            $error = true;
        }

        return $this->render('@Mobicoop/community/showCommunity.html.twig', [
            'community' => $community,
            'formIdentification' => $form->createView(),
            'communityUser' =>$communityUser
        ]);
    }
}
