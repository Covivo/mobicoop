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

use App\Community\Service\CommunityManager;
use App\Event\Service\EventManager;
use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Image\Entity\Image;
use App\MassCommunication\Service\CampaignManager;
use App\RelayPoint\Service\RelayPointManager;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ImageVoter extends Voter
{
    const POST = 'image_post';
    const READ = 'image_read';
    const UPDATE = 'image_update';
    const DELETE = 'image_delete';
    const ADMIN_MANAGE_EVENT = 'image_admin_manage_event';
    const ADMIN_MANAGE_COMMUNITY = 'image_admin_manage_community';
    const ADMIN_MANAGE_USER = 'image_admin_manage_user';

    private $permissionManager;
    private $userManager;
    private $communityManager;
    private $eventManager;
    private $relayPointManager;
    private $campaignManager;
    private $request;

    public function __construct(
        Security $security,
        PermissionManager $permissionManager,
        RequestStack $requestStack,
        EventManager $eventManager,
        CommunityManager $communityManager,
        UserManager $userManager,
        RelayPointManager $relayPointManager,
        CampaignManager $campaignManager
    ) {
        $this->security = $security;
        $this->permissionManager = $permissionManager;
        $this->userManager = $userManager;
        $this->communityManager = $communityManager;
        $this->eventManager = $eventManager;
        $this->relayPointManager = $relayPointManager;
        $this->campaignManager = $campaignManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject)
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
        // only for items actions
        if (in_array($attribute, [
            self::READ,
            self::UPDATE,
            self::DELETE,
            ]) && !$subject instanceof Image) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();
        
        switch ($attribute) {
            case self::READ:
                return $this->canRead($requester, $subject);
            case self::POST:
                return $this->canPost($requester, $this->request);
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
    
    private function canRead(UserInterface $requester, Image $subject)
    {
        if (($subject->getEventId() && $subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_read', $requester);
        } elseif (($subject->getCommunityId() && $subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_read', $requester);
        } elseif (($subject->getUserId() && $subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_read_self', $requester);
        }
        return false;
    }

    private function canPost(UserInterface $requester, Request $request)
    {
        if ($request->get('userId')) {
            if (!$this->userManager->getUser($request->get('userId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        if ($request->get('communityId')) {
            if (!$this->communityManager->getCommunity($request->get('userId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        if ($request->get('eventId')) {
            if (!$this->eventManager->getEvent($request->get('userId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        if ($request->get('relayPointId')) {
            if (!$this->relayPointManager->getRelayPoint($request->get('relayPointId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        if ($request->get('relayPointTypeId')) {
            if (!$this->userManager->getUser($request->get('userId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        if ($request->get('campaignId')) {
            if (!$this->userManager->getUser($request->get('userId'))) {
                return false;
            }
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        return false;
    }

    private function canUpdate(UserInterface $requester, Image $subject)
    {
        if (($subject->getEventId() && $subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_update_self', $requester);
        } elseif (($subject->getCommunityId() && $subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_update_self', $requester);
        } elseif (($subject->getUserId() && $subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_update_self', $requester);
        }
        return false;
    }

    private function canDelete(UserInterface $requester, Image $subject)
    {
        if (($subject->getEventId() && $subject->getEvent()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('event_manage', $requester))) {
            return $this->permissionManager->checkPermission('event_delete_self', $requester);
        } elseif (($subject->getCommunityId() && $subject->getCommunity()->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('community_manage', $requester))) {
            return $this->permissionManager->checkPermission('community_delete_self', $requester);
        } elseif (($subject->getUserId() && $subject->getUser()->getEmail() == $requester->getUsername()) || ($this->permissionManager->checkPermission('user_manage', $requester))) {
            return $this->permissionManager->checkPermission('user_delete_self', $requester);
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
