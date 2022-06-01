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
use App\Carpool\Entity\MapsAd\MapsAds;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityVoter extends Voter
{
    public const COMMUNITY_CREATE = 'community_create';
    public const COMMUNITY_READ = 'community_read';
    public const COMMUNITY_UPDATE = 'community_update';
    public const COMMUNITY_DELETE = 'community_delete';
    public const COMMUNITY_LIST = 'community_list';
    public const COMMUNITY_ADS = 'community_ads';
    public const COMMUNITY_LAST_MEMBERS = 'community_last_members';

    private $request;
    private $communityManager;

    public function __construct(AuthManager $authManager, RequestStack $requestStack, CommunityManager $communityManager)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->communityManager = $communityManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::COMMUNITY_CREATE,
            self::COMMUNITY_READ,
            self::COMMUNITY_UPDATE,
            self::COMMUNITY_DELETE,
            self::COMMUNITY_LIST,
            self::COMMUNITY_ADS,
            self::COMMUNITY_LAST_MEMBERS
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::COMMUNITY_CREATE,
            self::COMMUNITY_READ,
            self::COMMUNITY_UPDATE,
            self::COMMUNITY_DELETE,
            self::COMMUNITY_LIST,
            self::COMMUNITY_ADS,
            self::COMMUNITY_LAST_MEMBERS
            ]) && !($subject instanceof Paginator) && !($subject instanceof Community) && !($subject instanceof MapsAds) && !is_array($subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
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
                if (count($subject->getMapsAds())==0) {
                    // No Ads
                    return true;
                }
                return $this->canReadCommunity($this->communityManager->getCommunity($subject->getMapsAds()[0]->getEntityId()));
            case self::COMMUNITY_LAST_MEMBERS:
                if (count($subject)==0) {
                    // No members
                    return true;
                }
                return $this->canReadCommunity($subject[0]->getCommunity());
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

    private function canUpdateCommunity($subject)
    {
        if ($subject instanceof Community) {
            return $this->authManager->isAuthorized(self::COMMUNITY_UPDATE, ['community'=>$subject]);
        } elseif ($subject instanceof CommunityUser) {
            return $this->authManager->isAuthorized(self::COMMUNITY_UPDATE, ['community'=>$subject->getCommunity()]);
        }
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
