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

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Right\Service\PermissionManager;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Communication\Entity\Message;

class MessageVoter extends Voter
{
    const MESSAGE_CREATE = 'message_create';
    const MESSAGE_LIST = 'message_list';
    const MESSAGE_READ = 'message_read';
    
    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::MESSAGE_CREATE,
            self::MESSAGE_LIST,
            self::MESSAGE_READ
            ])) {
            return false;
        }

        // only vote on Message objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::MESSAGE_CREATE,
            self::MESSAGE_LIST,
            self::MESSAGE_READ
            ]) && !($subject instanceof Paginator) && !($subject instanceof Message)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::MESSAGE_CREATE:
                return $this->canCreateMessage($requester);
            case self::MESSAGE_LIST:
                return $this->canReadMessages($requester);
            case self::MESSAGE_READ:
                return $this->canReadMessage($requester, $subject);
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateMessage(UserInterface $requester)
    {
        // only registered users/apps can create messages
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('user_message_create', $requester);
    }

    private function canReadMessages(UserInterface $requester)
    {
        // only registered users/apps can create messages
        if (!$requester instanceof UserInterface) {
            return false;
        }
        if ($this->permissionManager->checkPermission('user_message_read', $requester)) {
            // we have to check that the user is allowed to read the messages, eg. he is the owner or one of the recipients
            return true;
        }
        return false;
    }

    private function canReadMessage(UserInterface $requester, Message $message)
    {
        // only registered users/apps can create messages
        if (!$requester instanceof UserInterface) {
            return false;
        }
        if ($this->permissionManager->checkPermission('user_message_read', $requester)) {
            // we have to check that the user is allowed to read the message, eg. he is the owner or one of the recipients
            return true;
        }
        return false;
    }
}
