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
 */

namespace App\Community\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Community\Entity\Community;
use App\Community\Repository\CommunityRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommunityVoter extends Voter
{
    public const ADMIN_COMMUNITY_CREATE = 'admin_community_create';
    public const ADMIN_COMMUNITY_READ = 'admin_community_read';
    public const ADMIN_COMMUNITY_UPDATE = 'admin_community_update';
    public const ADMIN_COMMUNITY_DELETE = 'admin_community_delete';
    public const ADMIN_COMMUNITY_LIST = 'admin_community_list';
    public const ADMIN_COMMUNITY_MEMBERSHIP = 'admin_community_membership';
    public const COMMUNITY_CREATE = 'community_create';
    public const COMMUNITY_READ = 'community_read';
    public const COMMUNITY_UPDATE = 'community_update';
    public const COMMUNITY_DELETE = 'community_delete';
    public const COMMUNITY_LIST = 'community_list';
    public const COMMUNITY_MEMBERSHIP = 'community_membership';

    private $authManager;
    private $request;
    private $communityRepository;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, CommunityRepository $communityRepository)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->communityRepository = $communityRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_CREATE,
            self::ADMIN_COMMUNITY_READ,
            self::ADMIN_COMMUNITY_UPDATE,
            self::ADMIN_COMMUNITY_DELETE,
            self::ADMIN_COMMUNITY_LIST,
            self::ADMIN_COMMUNITY_MEMBERSHIP,
        ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_CREATE,
            self::ADMIN_COMMUNITY_READ,
            self::ADMIN_COMMUNITY_UPDATE,
            self::ADMIN_COMMUNITY_DELETE,
            self::ADMIN_COMMUNITY_LIST,
            self::ADMIN_COMMUNITY_MEMBERSHIP,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Community)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_COMMUNITY_CREATE:
                return $this->canCreateCommunity();

            case self::ADMIN_COMMUNITY_READ:
                // this voter is used for direct community read, or for community member list, we have to check the type of subject
                if ($subject instanceof Community) {
                    return $this->canReadCommunity($subject);
                }
                if ($community = $this->communityRepository->find($this->request->get('id'))) {
                    return $this->canReadCommunity($community);
                }

                return false;

            case self::ADMIN_COMMUNITY_UPDATE:
                return $this->canUpdateCommunity($subject);

            case self::ADMIN_COMMUNITY_DELETE:
                return $this->canDeleteCommunity($subject);

            case self::ADMIN_COMMUNITY_LIST:
                return $this->canListCommunity();

            case self::ADMIN_COMMUNITY_MEMBERSHIP:
                return $this->canAddMember();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateCommunity()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_CREATE);
    }

    private function canReadCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_READ, ['community' => $community]);
    }

    private function canUpdateCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_UPDATE, ['community' => $community]);
    }

    private function canDeleteCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_DELETE, ['community' => $community]);
    }

    private function canListCommunity()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_LIST);
    }

    private function canAddMember()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_MEMBERSHIP);
    }
}
