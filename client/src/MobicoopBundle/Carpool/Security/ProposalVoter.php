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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Permission\Service\PermissionManager;

class ProposalVoter extends Voter
{
    const CREATE_AD = 'create_ad';
    const DELETE_AD = 'delete_ad';
    const POST = 'post';
    const POST_DELEGATE = 'post_delegate';
    const RESULTS = 'results';

    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CREATE_AD,
            self::DELETE_AD,
            self::POST,
            self::POST_DELEGATE,
            self::RESULTS
            ])) {
            return false;
        }

        // only vote on Proposal objects inside this voter
        if (!$subject instanceof Proposal) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        $proposal = $subject;

        switch ($attribute) {
            case self::CREATE_AD:
                return $this->canCreateProposal();
            case self::DELETE_AD:
                return $this->canDeleteProposal($proposal, $user);
            case self::POST:
                return $this->canPostProposal($user);
            case self::POST_DELEGATE:
                return $this->canPostDelegateProposal($user);
            case self::RESULTS:
                return $this->canViewProposalResults($proposal, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateProposal()
    {
        // everbody can create a proposal
        return true;
    }

    private function canDeleteProposal(Proposal $proposal, User $user)
    {
        // only registered users can delete proposal
        if (!$user instanceof User) {
            return false;
        }
        // only the author of the proposal can delete the proposal
        if ($proposal->getUser()->getId() !== $user->getId()) {
            return false;
        }
        return $this->permissionManager->checkPermission('proposal_delete_self', $user);
    }

    private function canPostProposal(User $user)
    {
        // only registered users can post a proposal
        if (!$user instanceof User) {
            return false;
        }
        return true;
    }

    private function canPostDelegateProposal(User $user)
    {
        // only dedicated users can post a proposal for another user
        if (!$user instanceof User) {
            return false;
        }
        return $this->permissionManager->checkPermission('proposal_post_delegate', $user);
    }

    private function canViewProposalResults(Proposal $proposal, User $user)
    {
        // only registered users can view proposal results
        if (!$user instanceof User) {
            return false;
        }
        // only the author of the proposal or a dedicated user can view the results
        if (($proposal->getUser()->getId() != $user->getId()) && (!$this->permissionManager->checkPermission('proposal_results_delegate', $user))) {
            return false;
        }
        return $this->permissionManager->checkPermission('proposal_results', $user);
    }
}
