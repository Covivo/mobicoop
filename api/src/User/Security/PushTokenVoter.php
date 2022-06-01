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

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\User\Entity\PushToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PushTokenVoter extends Voter
{
    public const PUSH_TOKEN_CREATE = 'push_token_create';
    public const PUSH_TOKEN_DELETE = 'push_token_delete';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::PUSH_TOKEN_CREATE,
            self::PUSH_TOKEN_DELETE
            ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::PUSH_TOKEN_CREATE,
            self::PUSH_TOKEN_DELETE
            ]) && !($subject instanceof PushToken)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::PUSH_TOKEN_CREATE:
                return $this->canCreatePushToken();
            case self::PUSH_TOKEN_DELETE:
                return $this->canDeletePushToken($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreatePushToken()
    {
        return $this->authManager->isAuthorized(self::PUSH_TOKEN_CREATE);
    }

    private function canDeletePushToken(PushToken $pushToken)
    {
        return $this->authManager->isAuthorized(self::PUSH_TOKEN_DELETE, ['pushToken'=>$pushToken]);
    }
}
