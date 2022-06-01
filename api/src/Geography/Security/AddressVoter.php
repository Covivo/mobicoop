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

namespace App\Geography\Security;

use App\Auth\Service\PermissionManager;
use App\Geography\Entity\Address;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class AddressVoter extends Voter
{
    public const POST = 'address_post';
    public const READ = 'address_read';
    public const UPDATE = 'address_update';
    public const DELETE = 'address_delete';
    public const ADMIN_MANAGE_EVENT = 'image_admin_manage_event';
    public const ADMIN_MANAGE_COMMUNITY = 'image_admin_manage_community';
    public const ADMIN_MANAGE_USER = 'image_admin_manage_user';

    private $security;
    private $permissionManager;

    public function __construct(Security $security, PermissionManager $permissionManager)
    {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::POST,
            self::READ,
            self::UPDATE,
            self::DELETE,
            self::ADMIN_MANAGE_EVENT,
            self::ADMIN_MANAGE_COMMUNITY,
            self::ADMIN_MANAGE_USER,
        ])) {
            return false;
        }

        // only vote on Image objects inside this voter
        if (!$subject instanceof Address) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // TO DO : Code the real Voter
        return true;
        $requester = $token->getUser();

        switch ($attribute) {
            case self::POST:
                return $this->canPost($requester, $subject);

            case self::READ:
                return $this->canRead($requester, $subject);

            case self::UPDATE:
                return $this->canUpdate($requester, $subject);

            case self::DELETE:
                return $this->canDelete($requester, $subject);

            case self::ADMIN_MANAGE_EVENT:
                return $this->canAdminManageEvent($requester, $subject);

            case self::ADMIN_MANAGE_COMMUNITY:
                return $this->canAdminManageCommunity($requester, $subject);

            case self::ADMIN_MANAGE_USER:
                return $this->canAdminManageUser($requester, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost(UserInterface $requester, Address $subject)
    {
        if (($subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_create', $requester);
        }
        if (($subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_create', $requester);
        }
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_address_create_self', $requester);
        }

        return false;
    }

    private function canRead(UserInterface $requester, Address $subject)
    {
        if (($subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_read', $requester);
        }
        if (($subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_read', $requester);
        }
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_read_self', $requester);
        }

        return false;
    }

    private function canUpdate(UserInterface $requester, Address $subject)
    {
        if (($subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_update_self', $requester);
        }
        if (($subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_update_self', $requester);
        }
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_address_update_self', $requester);
        }

        return false;
    }

    private function canDelete(UserInterface $requester, Address $subject)
    {
        if (($subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_delete_self', $requester);
        }
        if (($subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_delete_self', $requester);
        }
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_address_delete_self', $requester);
        }

        return false;
    }

    private function canAdminManageEvent($requester)
    {
        return $this->permissionManager->checkPermission('event_manage', $requester);
    }

    private function canAdminManageCommunity($requester)
    {
        return $this->permissionManager->checkPermission('community_manage', $requester);
    }

    private function canAdminManageUser($requester)
    {
        return $this->permissionManager->checkPermission('user_manage', $requester);
    }
}
