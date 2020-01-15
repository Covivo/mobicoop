<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Carpool\Entity\Ad;
use App\Carpool\Service\AdManager;
use App\Right\Service\PermissionManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AdVoter extends Voter
{
    const CREATE_AD = 'create_ad';
    const DELETE_AD = 'delete_ad';
    const POST_AD = 'post_ad';
    const POST_AD_DELEGATE = 'post_ad_delegate';
    const RESULTS_AD = 'results_ad';
    
    private $security;
    private $request;
    private $adManager;
    private $permissionManager;

    public function __construct(RequestStack $requestStack, Security $security, AdManager $adManager, PermissionManager $permissionManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->adManager = $adManager;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CREATE_AD,
            self::DELETE_AD,
            self::POST_AD,
            self::POST_AD_DELEGATE,
            self::RESULTS_AD
            ])) {
            return false;
        }

        // Ad is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$ad = $this->adManager->getAdForPermission($this->request->get("id"))) {
            return false;
        }

        $requester = $token->getUser();

        switch ($attribute) {
            case self::CREATE_AD:
                return $this->canCreateAd();
            case self::DELETE_AD:
                return $this->canDeleteAd($ad, $requester);
            case self::POST_AD:
                return $this->canPostAd($requester);
            case self::POST_AD_DELEGATE:
                return $this->canPostDelegateAd($requester);
            case self::RESULTS_AD:
                return $this->canViewAdResults($ad, $requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateAd()
    {
        // everbody can create a ad
        return true;
    }

    private function canDeleteAd(Ad $ad, UserInterface $requester)
    {
        // only registered users can delete ad
        if (!$requester instanceof User) {
            return false;
        }
        // only the author of the ad can delete the ad
        if ($ad->getUserId() !== $requester->getId()) {
            return false;
        }
        return $this->permissionManager->checkPermission('proposal_delete_self', $requester);
    }

    private function canPostAd(UserInterface $requester)
    {
        // only registered users can post a ad
        if (!$requester instanceof User) {
            return false;
        }
        return true;
    }

    private function canPostDelegateAd(UserInterface $requester)
    {
        // only dedicated users can post a ad for another user
        if (!$requester instanceof User) {
            return false;
        }
        return $this->permissionManager->checkPermission('proposal_post_delegate', $requester);
    }

    private function canViewAdResults(Ad $ad, UserInterface $requester)
    {
        // only registered users can view ad results
        if (!$requester instanceof User) {
            return false;
        }
        // only the author of the ad or a dedicated user can view the results
        if (($ad->getUserId() != $requester->getId()) && (!$this->permissionManager->checkPermission('proposal_results_delegate', $requester))) {
            return false;
        }
        
        return $this->permissionManager->checkPermission('proposal_results', $requester);
    }
}
