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
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class CommunityVoter extends Voter
{
    const ADMIN_COMMUNITY_READ = 'admin_community_read';
    const COMMUNITY_READ = 'community_read';

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
            self::ADMIN_COMMUNITY_READ
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_READ
            ]) && !($subject instanceof Paginator) && !($subject instanceof Community)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_COMMUNITY_READ:
                // here we don't have the denormalized event, we need to get it from the request
                if ($community = $this->communityManager->getCommunity($this->request->get('id'))) {
                    return $this->canReadCommunity($community);
                }
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadCommunity(Community $community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_READ, ['community'=>$community]);
    }
}
