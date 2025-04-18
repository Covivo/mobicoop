<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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
use App\Community\Entity\CommunityUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommunityUserVoter extends Voter
{
    // for now, update a member membership uses the community update right
    public const ADMIN_COMMUNITY_MEMBER_UPDATE = 'admin_community_member_update';
    public const ADMIN_COMMUNITY_MEMBER_DELETE = 'admin_community_member_delete';
    public const COMMUNITY_UPDATE = 'community_update';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_MEMBER_UPDATE,
            self::ADMIN_COMMUNITY_MEMBER_DELETE,
        ])) {
            return false;
        }

        // only vote on CommunityUser objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::ADMIN_COMMUNITY_MEMBER_UPDATE,
            self::ADMIN_COMMUNITY_MEMBER_DELETE,
        ]) && !($subject instanceof Paginator) && !($subject instanceof CommunityUser)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_COMMUNITY_MEMBER_UPDATE:
            case self::ADMIN_COMMUNITY_MEMBER_DELETE:
                // @var CommunityUser $subject
                return $this->canUpdateMember($subject->getCommunity());
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canUpdateMember($community)
    {
        return $this->authManager->isAuthorized(self::COMMUNITY_UPDATE, ['community' => $community]);
    }
}
