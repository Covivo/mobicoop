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

namespace App\Action\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Action\Entity\Action;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ActionVoter extends Voter
{
    public const ACTION_CREATE = 'action_create';
    public const ACTION_READ = 'action_read';
    public const ACTION_UPDATE = 'action_update';
    public const ACTION_DELETE = 'action_delete';
    public const ACTION_LIST = 'action_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ACTION_CREATE,
            self::ACTION_READ,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_LIST,
        ])) {
            return false;
        }

        // only vote on Action objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::ACTION_CREATE,
            self::ACTION_READ,
            self::ACTION_UPDATE,
            self::ACTION_DELETE,
            self::ACTION_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Action)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ACTION_CREATE:
                return $this->canCreateAction();

            case self::ACTION_READ:
                return $this->canReadAction($subject);

            case self::ACTION_UPDATE:
                return $this->canUpdateAction($subject);

            case self::ACTION_DELETE:
                return $this->canDeleteAction($subject);

            case self::ACTION_LIST:
                return $this->canListAction();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateAction()
    {
        return $this->authManager->isAuthorized(self::ACTION_CREATE);
    }

    private function canReadAction(Action $action)
    {
        return $this->authManager->isAuthorized(self::ACTION_READ, ['action' => $action]);
    }

    private function canUpdateAction(Action $action)
    {
        return $this->authManager->isAuthorized(self::ACTION_UPDATE, ['action' => $action]);
    }

    private function canDeleteAction(Action $action)
    {
        return $this->authManager->isAuthorized(self::ACTION_DELETE, ['action' => $action]);
    }

    private function canListAction()
    {
        return $this->authManager->isAuthorized(self::ACTION_LIST);
    }
}
