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

namespace Mobicoop\Bundle\MobicoopBundle\User\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\UserAddress;
use Mobicoop\Bundle\MobicoopBundle\User\Form\UserForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Point;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\ProposalForm;

/**
 * Controller class for user related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class UserController extends AbstractController
{
    /**
     * Retrieve a user.
     *
     * @Route("/user/{id}", name="user", requirements={"id"="\d+"})
     *
     */
    public function user($id, UserManager $userManager)
    {
        return $this->render('@Mobicoop/user/detail.html.twig', [
            'user' => $userManager->getUser($id)
        ]);
    }
    
    /**
     * Retrieve all users.
     *
     * @Route("/users", name="users")
     *
     */
    public function users(UserManager $userManager)
    {
        return $this->render('@Mobicoop/user/index.html.twig', [
            'hydra' => $userManager->getUsers()
        ]);
    }
    
    /**
     * Create a user.
     *
     * @Route("/user/create", name="user_create")
     *
     */
    public function userCreate(UserManager $userManager, Request $request)
    {
        $user = new User();
        
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        $error = false;
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->createUser($user)) {
                return $this->redirectToRoute('users');
            }
            $error = true;
        }
        
        return $this->render('@Mobicoop/user/create.html.twig', [
                'form' => $form->createView(),
                'error' => $error
        ]);
    }
    
    /**
     * Update a user.
     *
     * @Route("/user/{id}/update", name="user_update", requirements={"id"="\d+"})
     *
     */
    public function userUpdate($id, UserManager $userManager, Request $request)
    {
        $user = $userManager->getUser($id);
        
        $form = $this->createForm(UserForm::class, $user);
        $form->handleRequest($request);
        $error = false;
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($userManager->updateUser($user)) {
                return $this->redirectToRoute('users');
            }
            $error = true;
        }
        
        return $this->render('@Mobicoop/user/update.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
                'error' => $error
        ]);
    }
    
    /**
     * Delete a user.
     *
     * @Route("/user/{id}/delete", name="user_delete", requirements={"id"="\d+"})
     *
     */
    public function userDelete($id, UserManager $userManager)
    {
        if ($userManager->deleteUser($id)) {
            return $this->redirectToRoute('users');
        } else {
            return $this->render('@Mobicoop/user/index.html.twig', [
                    'hydra' => $userManager->getUsers(),
                    'error' => 'An error occured'
            ]);
        }
    }

    /**
     * Create a proposal for a user.
     *
     * @Route("/user/{id}/proposal/create", name="user_proposal_create", requirements={"id"="\d+"})
     *
     */
    public function userProposalCreate($id, ProposalManager $proposalManager, Request $request)
    {
        $proposal = new Proposal();
        $proposal->setUser(new User($id));

        $form = $this->createForm(ProposalForm::class, $proposal);
        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted() && $form->isValid()) {
            // for now we add the starting end ending points,
            // in the future we will need to have dynamic fields
            $proposal->addPoint($proposal->getStart());
            $proposal->addPoint($proposal->getDestination());
            if ($proposal = $proposalManager->createProposal($proposal)) {
                return $this->redirectToRoute('user_proposal_matchings', ['id'=>$id,'idProposal'=>$proposal->getId()]);
            }
            $error = true;
        }

        return $this->render('@Mobicoop/proposal/create.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * Retrieve all proposals for a user.
     *
     * @Route("/user/{id}/proposals", name="user_proposals", requirements={"id"="\d+"})
     *
     */
    public function userProposals($id, ProposalManager $proposalManager)
    {
        $user = new User($id);
        return $this->render('@Mobicoop/proposal/index.html.twig', [
            'user' => $user,
            'hydra' => $proposalManager->getProposals($user)
        ]);
    }

    /**
     * Retrieve all matchings for a proposal.
     *
     * @Route("/user/{id}/proposal/{idProposal}/matchings", name="user_proposal_matchings", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    public function userProposalMatchings($id, $idProposal, ProposalManager $proposalManager)
    {
        $user = new User($id);
        $proposal = $proposalManager->getProposal($idProposal);
        return $this->render('@Mobicoop/proposal/matchings.html.twig', [
            'user' => $user,
            'proposal' => $proposal,
            'hydra' => $proposalManager->getMatchings($proposal)
        ]);
    }

    /**
     * Delete a proposal of a user.
     *
     * @Route("/user/{id}/proposal/{idProposal}/delete", name="user_proposal_delete", requirements={"id"="\d+","idProposal"="\d+"})
     *
     */
    public function userProposalDelete($id, $idProposal, ProposalManager $proposalManager)
    {
        if ($proposalManager->deleteProposal($idProposal)) {
            return $this->redirectToRoute('user_proposals', ['id'=>$id]);
        } else {
            $user = new User($id);
            return $this->render('@Mobicoop/proposal/index.html.twig', [
                'user' => $user,
                'hydra' => $proposalManager->getProposals($user),
                'error' => 'An error occured'
            ]);
        }
    }
}
