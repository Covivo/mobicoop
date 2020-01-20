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

namespace App\Event\Security;

use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Event\Entity\Event;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    const CREATE = 'event_create';
    const READ = 'event_read';
    const UPDATE = 'event_update';
    const DELETE = 'event_delete';
    const ADMIN_MANAGE = 'event_admin_manage';

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
            self::ADMIN_MANAGE
            ])) {
            return false;
        }

        // only vote on Event objects inside this voter
        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::CREATE:
                return $this->canPost($requester);
            case self::READ:
                return $this->canRead($requester);
            case self::UPDATE:
                return $this->canUpdate($requester, $subject);
            case self::DELETE:
                return $this->canDelete($requester, $subject);
            case self::ADMIN_MANAGE:
                return $this->canAdminManage($requester);
           
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost($requester)
    {
        return $this->permissionManager->checkPermission('event_create', $requester);
    }

    private function canRead($requester)
    {
        return $this->permissionManager->checkPermission('event_read', $requester);
    }

    private function canUpdate($requester, $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_update_self', $requester);
        } else {
            return false;
        }
    }
    
    private function canDelete($requester, $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_delete_self', $requester);
        } else {
            return false;
        }
    }

    private function canAdminManage($requester)
    {
        return $this->permissionManager->checkPermission('event_manage', $requester);
    }
}
