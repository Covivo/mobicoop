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

namespace App\Image\Security;

use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Image\Entity\Image;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    const POST = 'image_post';
    const UPDATE = 'image_update';
    const DELETE = 'image_delete';

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
            self::POST,
            self::UPDATE,
            self::DELETE
            ])) {
            return false;
        }
        // only vote on Image objects inside this voter
        if (!$subject instanceof Image) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();
        
        switch ($attribute) {
            case self::POST:
                return $this->canPost($requester, $subject);
            case self::POST:
                return $this->canUpdate($requester, $subject);
            case self::POST:
                return $this->canDelete($requester, $subject);
        
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canPost(UserInterface $requester, Image $subject)
    {
        if ($subject->getEventId()) {
            return $this->permissionManager->checkPermission('event_create', $requester);
        } elseif ($subject->getCommunityId()) {
            return $this->permissionManager->checkPermission('community_create', $requester);
        } elseif ($subject->getUserId()) {
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        return false;
    }

    private function canUpdate(UserInterface $requester, Image $subject)
    {
        if ($subject->getEventId()) {
            return $this->permissionManager->checkPermission('event_update_self', $requester);
        } elseif ($subject->getCommunityId()) {
            return $this->permissionManager->checkPermission('community_update_self', $requester);
        } elseif ($subject->getUserId()) {
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        return false;
    }

    private function canDelete(UserInterface $requester, Image $subject)
    {
        if ($subject->getEventId()) {
            return $this->permissionManager->checkPermission('event_delete_self', $requester);
        } elseif ($subject->getCommunityId()) {
            return $this->permissionManager->checkPermission('community_delete_self', $requester);
        } elseif ($subject->getUserId()) {
            return $this->permissionManager->checkPermission('user_delete_self', $requester);
        }
        return false;
    }
}
