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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Permission\Service\PermissionManager;

class AdVoter extends Voter
{
    const CREATE_AD = 'create_ad';
    const DELETE_AD = 'delete_ad';
    const POST = 'post';
    const POST_DELEGATE = 'post_delegate';
    const RESULTS = 'results_ad';

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

        // only vote on Ad objects inside this voter
        if (!$subject instanceof Ad) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        $ad = $subject;

        switch ($attribute) {
            case self::CREATE_AD:
                return $this->canCreateAd();
            case self::DELETE_AD:
                return $this->canDeleteAd($ad, $user);
            case self::POST:
                return $this->canPostAd($user);
            case self::POST_DELEGATE:
                return $this->canPostDelegateAd($user);
            case self::RESULTS:
                return $this->canViewAdResults($ad, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateAd()
    {
        // everbody can create a ad
        return true;
    }

    private function canDeleteAd(Ad $ad, User $user)
    {
        // only registered users can delete ad
        if (!$user instanceof User) {
            return false;
        }
        return $this->permissionManager->checkPermission('ad_delete', $user, $ad->getId());
    }

    private function canPostAd(User $user)
    {
        // only registered users can post a ad
        if (!$user instanceof User) {
            return false;
        }
        return true;
    }

    private function canPostDelegateAd(User $user)
    {
        // only dedicated users can post a ad for another user
        if (!$user instanceof User) {
            return false;
        }
        return $this->permissionManager->checkPermission('ad_create', $user);
    }

    private function canViewAdResults(Ad $ad, User $user)
    {
        // only registered users can view ad results
        if (!$user instanceof User) {
            return false;
        }
        
        return $this->permissionManager->checkPermission('ad_results', $user, $ad->getId());
    }
}
