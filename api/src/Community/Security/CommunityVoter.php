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

namespace App\Community\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Community\Entity\Community;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityVoter extends Voter
{
    const COMMUNITY_CREATE = 'community_create';
    const COMMUNITY_READ = 'community_read';
    const COMMUNITY_UPDATE = 'community_update';
    const COMMUNITY_DELETE = 'community_delete';
    const COMMUNITY_LIST = 'community_list';
    const COMMUNITY_ADS = 'community_ads';

    private $permissionManager;
    private $request;
    private $communityManager;

    public function __construct(AuthManager $authManager, RequestStack $requestStack, CommunityManager $communityManager)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::COMMUNITY_CREATE,
            self::COMMUNITY_READ,
            self::COMMUNITY_UPDATE,
            self::COMMUNITY_DELETE,
            self::COMMUNITY_LIST,
            self::COMMUNITY_ADS
            ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::COMMUNITY_CREATE,
            self::COMMUNITY_READ,
            self::COMMUNITY_UPDATE,
            self::COMMUNITY_DELETE,
            self::COMMUNITY_LIST,
            self::COMMUNITY_ADS
            ]) && !($subject instanceof Paginator) && !($subject instanceof Community)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::COMMUNITY_CREATE:
                return $this->canCreateCommunity();
            case self::COMMUNITY_READ:
                return $this->canReadCommunity($subject);
            case self::COMMUNITY_UPDATE:
                return $this->canUpdateCommunity($subject);
            case self::COMMUNITY_DELETE:
                return $this->canDeleteCommunity($subject);
            case self::COMMUNITY_LIST:
                return $this->canListCommunity();
            case self::COMMUNITY_ADS:
                // here we don't have the denormalized event, we need to get it from the request
                if ($community = $this->communityManager->getCommunity($this->request->get('id'))) {
                    return $this->canReadCommunity($community);
                }
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateCommunity()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_CREATE);
    }

    private function canReadCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_READ, ['community'=>$community]);
    }

    private function canUpdateCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_UPDATE, ['community'=>$community]);
    }

    private function canDeleteCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_DELETE, ['community'=>$community]);
    }

    private function canListCommunity()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_LIST);
    }
}
