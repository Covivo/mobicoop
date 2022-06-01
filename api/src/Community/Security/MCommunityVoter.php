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
use App\Community\Resource\MCommunity;
use App\Community\Service\CommunityManager;
use Symfony\Component\HttpFoundation\RequestStack;

class MCommunityVoter extends Voter
{
    public const COMMUNITY_LIST = 'community_list';

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
            self::COMMUNITY_LIST
            ])) {
            return false;
        }

        // only vote on Community objects inside this voter
        if (!in_array($attribute, [
            self::COMMUNITY_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof MCommunity)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::COMMUNITY_LIST:
                return $this->canListCommunity();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canListCommunity()
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_LIST);
    }
}
