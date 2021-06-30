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
 **************************/

namespace App\Gamification\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Gamification\Entity\Badge;

class BadgeVoter extends Voter
{
    const BADGE_CREATE = 'badge_create';
    const BADGE_READ = 'badge_read';
    const BADGE_UPDATE = 'badge_update';
    const BADGE_DELETE = 'badge_delete';
    const BADGE_LIST = 'badge_list';
    const BADGES_BOARD = 'badges_board';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::BADGE_CREATE,
            self::BADGE_READ,
            self::BADGE_UPDATE,
            self::BADGE_DELETE,
            self::BADGE_LIST,
            self::BADGES_BOARD,
            ])) {
            return false;
        }

        // only vote on Event objects inside this voter
        if (!in_array($attribute, [
            self::BADGE_CREATE,
            self::BADGE_READ,
            self::BADGE_UPDATE,
            self::BADGE_DELETE,
            self::BADGE_LIST,
            self::BADGES_BOARD,
            ]) && !($subject instanceof Paginator) && !($subject instanceof Badge)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::BADGE_CREATE:
                return $this->canCreate();
            case self::BADGE_READ:
                return $this->canRead($subject);
            case self::BADGE_UPDATE:
                return $this->canUpdate($subject);
            case self::BADGE_DELETE:
                return $this->canDelete($subject);
            case self::BADGE_LIST:
                return $this->canList();
            case self::BADGES_BOARD:
                return $this->canGetBoard();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreate()
    {
        return $this->authManager->isAuthorized(self::BADGE_CREATE);
    }

    private function canRead(Badge $badge)
    {
        return $this->authManager->isAuthorized(self::BADGE_READ, ['badge'=>$badge]);
    }

    private function canUpdate(Badge $badge)
    {
        return $this->authManager->isAuthorized(self::BADGE_UPDATE, ['badge'=>$badge]);
    }
    
    private function canDelete(Badge $badge)
    {
        return $this->authManager->isAuthorized(self::BADGE_DELETE, ['badge'=>$badge]);
    }
    
    private function canList()
    {
        return $this->authManager->isAuthorized(self::BADGE_LIST);
    }

    private function canGetBoard()
    {
        return $this->authManager->isAuthorized(self::BADGES_BOARD);
    }
}
