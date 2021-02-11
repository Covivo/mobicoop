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

namespace App\Community\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Community\Entity\Community;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityVoter extends Voter
{
    const ADMIN_COMMUNITY_CREATE = 'admin_community_create';
    const ADMIN_COMMUNITY_READ = 'admin_community_read';
    const ADMIN_COMMUNITY_UPDATE = 'admin_community_update';
    const ADMIN_COMMUNITY_DELETE = 'admin_community_delete';
    const ADMIN_COMMUNITY_LIST = 'admin_community_list';
    const COMMUNITY_CREATE = 'community_create';
    const COMMUNITY_READ = 'community_read';
    const COMMUNITY_UPDATE = 'community_update';
    const COMMUNITY_DELETE = 'community_delete';
    const COMMUNITY_LIST = 'community_list';

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
            self::ADMIN_COMMUNITY_CREATE,
            self::ADMIN_COMMUNITY_READ,
            self::ADMIN_COMMUNITY_UPDATE,
            self::ADMIN_COMMUNITY_DELETE,
            self::ADMIN_COMMUNITY_LIST
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_CREATE,
            self::ADMIN_COMMUNITY_READ,
            self::ADMIN_COMMUNITY_UPDATE,
            self::ADMIN_COMMUNITY_DELETE,
            self::ADMIN_COMMUNITY_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Community)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_COMMUNITY_CREATE:
                return $this->canCreateCommunity();
            case self::ADMIN_COMMUNITY_READ:
                // here we don't have the denormalized event, we need to get it from the request
                if ($community = $this->communityManager->getCommunity($this->request->get('id'))) {
                    return $this->canReadCommunity($community);
                }
                // no break
            case self::ADMIN_COMMUNITY_UPDATE:
                // here we don't have the denormalized event, we need to get it from the request
                if ($community = $this->communityManager->getCommunity($this->request->get('id'))) {
                    return $this->canUpdateCommunity($community);
                }
                // no break
            case self::ADMIN_COMMUNITY_DELETE:
                // here we don't have the denormalized event, we need to get it from the request
                if ($community = $this->communityManager->getCommunity($this->request->get('id'))) {
                    return $this->canDeleteCommunity($community);
                }
                // no break
            case self::ADMIN_COMMUNITY_LIST:
                return $this->canListCommunity();
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
