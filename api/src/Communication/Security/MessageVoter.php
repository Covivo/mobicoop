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

namespace App\Communication\Security;

use App\Auth\Service\AuthManager;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Communication\Entity\Message;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MessageVoter extends Voter
{
    const USER_MESSAGE_CREATE = 'user_message_create';
    const USER_MESSAGE_READ = 'user_message_read';
    const USER_MESSAGE_DELETE = 'user_message_delete';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::USER_MESSAGE_CREATE,
            self::USER_MESSAGE_READ,
            self::USER_MESSAGE_DELETE
            ])) {
            return false;
        }

        // only vote on Message objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::USER_MESSAGE_CREATE,
            self::USER_MESSAGE_READ,
            self::USER_MESSAGE_DELETE
            ]) && !($subject instanceof Paginator) && !($subject instanceof Message)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::USER_MESSAGE_CREATE:
                return $this->canCreateMessage($subject);
            case self::USER_MESSAGE_READ:
                if (is_array($subject)) {
                    // If this is a complete thread we are sending the first message to check the permission
                    (is_array($subject)) ? $message = $subject[0] : $message = $subject;
                    return $this->canReadMessage($message);
                } else {
                    return $this->canReadMessage($subject);
                }
                // no break
            case self::USER_MESSAGE_DELETE:
                return $this->canDeleteMessage($subject);
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateMessage(Message $message)
    {
        return $this->authManager->isAuthorized(self::USER_MESSAGE_CREATE, ['message'=>$message]);
    }

    private function canReadMessage(Message $message)
    {
        return $this->authManager->isAuthorized(self::USER_MESSAGE_READ, ['message'=>$message]);
    }

    private function canDeleteMessage(Message $message)
    {
        return $this->authManager->isAuthorized(self::USER_MESSAGE_DELETE, ['message'=>$message]);
    }
}
