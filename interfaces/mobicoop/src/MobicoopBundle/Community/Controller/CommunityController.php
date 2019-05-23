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
 *    along with this program.  If not, see <gnu.oruse Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;g/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Community\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Service\CommunityManager;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Community\Form\CommunityForm;

/**
 * Controller class for community related actions.
 *
 */
class CommunityController extends AbstractController
{
    /**
     *
     * Get all communities.
     */
    public function list(CommunityManager $communityManager)
    {
        return $this->render('@Mobicoop/community/communities.html.twig', [
            'hydra' => $communityManager->getCommunities(),
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * Create a community
     */
    public function create(CommunityManager $communityManager, UserManager $userManager, Request $request)
    {
        $community = new Community();
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
}
