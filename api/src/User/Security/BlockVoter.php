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

namespace App\User\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\User\Ressource\Block;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BlockVoter extends Voter
{
    public const BLOCK_CREATE = 'block_create';
    public const BLOCK_BLOCKED = 'block_blocked';
    public const BLOCK_BLOCKEDBY = 'block_blockedby';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::BLOCK_CREATE,
            self::BLOCK_BLOCKED,
            self::BLOCK_BLOCKEDBY,
        ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::BLOCK_CREATE,
            self::BLOCK_BLOCKED,
            self::BLOCK_BLOCKEDBY,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Block)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::BLOCK_CREATE:
                return $this->canCreateBlock();

            case self::BLOCK_BLOCKED:
                return $this->canBlockBlocked();

            case self::BLOCK_BLOCKEDBY:
                return $this->canBlockBlockedBy();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateBlock()
    {
        return $this->authManager->isAuthorized(self::BLOCK_CREATE);
    }

    private function canBlockBlocked()
    {
        return $this->authManager->isAuthorized(self::BLOCK_BLOCKED);
    }

    private function canBlockBlockedBy()
    {
        return $this->authManager->isAuthorized(self::BLOCK_BLOCKEDBY);
    }
}
