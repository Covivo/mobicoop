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
use App\Community\Entity\CommunityUser;

class CommunityUserVoter extends Voter
{
    const COMMUNITY_JOIN = 'community_join';
    const COMMUNITY_MEMBERSHIP= 'community_membership';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::COMMUNITY_JOIN,
            self::COMMUNITY_MEMBERSHIP
            ])) {
            return false;
        }

        // only vote on CommunityUser objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::COMMUNITY_JOIN,
            self::COMMUNITY_MEMBERSHIP
            ]) && !($subject instanceof Paginator) && !($subject instanceof CommunityUser)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::COMMUNITY_JOIN:
                return $this->canJoin($subject);
            case self::COMMUNITY_MEMBERSHIP:
                return $this->canAddCommunityUser();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canJoin($subject)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_JOIN);
    }

    private function canAddCommunityUser()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_MEMBERSHIP);
    }
}
