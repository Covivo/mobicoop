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
use App\Auth\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Community\Entity\Community;
use Symfony\Component\Security\Core\Security;

class CommunityVoter extends Voter
{
    const COMMUNITY_CREATE = 'community_create';
    const COMMUNITY_READ = 'community_read';
    const COMMUNITY_UPDATE = 'community_update';
    const COMMUNITY_DELETE = 'community_delete';
    const COMMUNITY_LIST = 'community_list';
    const COMMUNITY_JOIN = 'community_join';

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
            self::COMMUNITY_CREATE,
            self::COMMUNITY_READ,
            self::COMMUNITY_UPDATE,
            self::COMMUNITY_DELETE,
            self::COMMUNITY_LIST,
            self::COMMUNITY_JOIN,
            ])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        // TO DO : Code the real Voter
        return true;

        $requester = $token->getUser();

        switch ($attribute) {
            case self::COMMUNITY_CREATE:
                return $this->canPost($requester);
            case self::COMMUNITY_READ:
                return $this->canRead($requester);
            case self::COMMUNITY_UPDATE:
                return $this->canUpdate($requester, $subject);
            case self::COMMUNITY_DELETE:
                return $this->canDelete($requester, $subject);
            case self::COMMUNITY_LIST:
                return $this->canList($requester);
            case self::COMMUNITY_JOIN:
                return $this->canJoin($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost($requester)
    {
        return $this->permissionManager->checkPermission('community_create', $requester);
    }

    private function canRead($requester)
    {
        return $this->permissionManager->checkPermission('community_read', $requester);
    }

    private function canUpdate($requester, Community $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_update_self', $requester);
        } else {
            return false;
        }
    }
    
    private function canDelete($requester, Community $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_delete_self', $requester);
        } else {
            return false;
        }
    }

    private function canList($requester)
    {
        return $this->permissionManager->checkPermission('community_list', $requester);
    }

    private function canCheckExistence($requester)
    {
        return $this->permissionManager->checkPermission('community_read', $requester);
    }

    private function canJoin($requester)
    {
        return $this->permissionManager->checkPermission('community_join', $requester);
    }
}
