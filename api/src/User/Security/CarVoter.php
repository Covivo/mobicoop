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

namespace App\User\Security;

use App\Auth\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\User\Entity\User;
use App\User\Entity\Car;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CarVoter extends Voter
{
    public const CREATE = 'car_create';
    public const READ = 'car_read';
    public const UPDATE = 'car_update';
    public const DELETE = 'car_delete';
    public const ADMIN_READ = 'car_admin_read';

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
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DELETE,
            self::ADMIN_READ
            ])) {
            return false;
        }

        // only vote on Car objects inside this voter
        if (!$subject instanceof Car) {
            return false;
        }
        // var_dump($subject);die;
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // To DO : Code the real voter
        return true;

        $requester = $token->getUser();
        switch ($attribute) {
            case self::CREATE:
                return $this->canPostSelf($requester, $subject);
            case self::READ:
                return $this->canReadSelf($requester, $subject);
            case self::UPDATE:
                return $this->canUpdateSelf($requester, $subject);
            case self::DELETE:
                return $this->canDeleteSelf($requester, $subject);
            case self::DELETE:
                return $this->canAdminRead($requester, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPostSelf(UserInterface $requester, Car $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_car_create', $requester))) {
            return $this->permissionManager->checkPermission('user_car_create_self', $requester);
        } else {
            return false;
        }
    }

    private function canReadSelf(UserInterface $requester, Car $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_car_update', $requester))) {
            return $this->permissionManager->checkPermission('user_car_update_self', $requester);
        } else {
            return false;
        }
    }

    private function canUpdateSelf(UserInterface $requester, Car $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_car_update', $requester))) {
            return $this->permissionManager->checkPermission('user_car_update_self', $requester);
        } else {
            return false;
        }
    }

    private function canDeleteSelf(UserInterface $requester, Car $subject)
    {
        if (($subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_car_delete', $requester))) {
            return $this->permissionManager->checkPermission('user_car_delete_self', $requester);
        } else {
            return false;
        }
    }

    private function canAdminRead(UserInterface $requester, Car $subject)
    {
        return $this->permissionManager->checkPermission('user_car_manage', $requester);
    }
}
