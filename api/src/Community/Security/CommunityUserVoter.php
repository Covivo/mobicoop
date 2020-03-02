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
use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Community\Entity\CommunityUser;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CommunityUserVoter extends Voter
{
    const CREATE = 'communityUser_create';
    const READ = 'communityUser_read';
    const UPDATE = 'communityUser_update';
    const DELETE = 'communityUser_delete';
    const LIST = 'community_user_list';

    private $security;
    private $permissionManager;

    public function __construct(Security $security, PermissionManager $permissionManager)
    {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DELETE,
            self::LIST
            ])) {
            return false;
        }

        // only vote on Article objects inside this voter
        // only for items actions
        if (!($subject instanceof Paginator) && !($subject instanceof CommunityUser)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::CREATE:
                return $this->canPost($requester, $subject);
            case self::READ:
                return $this->canRead($requester);
            case self::UPDATE:
                return false;
            case self::DELETE:
                return $this->canDelete($requester, $subject);
            case self::LIST:
                return $this->canList($requester, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost($requester, $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername())) {
            return $this->permissionManager->checkPermission('community_join', $requester);
        } else {
            return false;
        }
    }

    private function canDelete($requester, $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername())) {
            return $this->permissionManager->checkPermission('community_leave', $requester);
        } else {
            return false;
        }
    }

    private function canRead($requester)
    {
        return $this->permissionManager->checkPermission('community_read', $requester);
    }

    private function canList($requester, $subject)
    {
        return $this->permissionManager->checkPermission('community_list', $requester);
    }
}
