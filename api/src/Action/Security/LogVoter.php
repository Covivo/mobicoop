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

use App\Action\Entity\Log;
use App\Auth\Service\PermissionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class LogVoter extends Voter
{
    public const READ_LOG = 'log_read';
    public const READ_LOGS = 'logs_read';

    private $security;
    private $request;
    private $permissionManager;

    public function __construct(RequestStack $requestStack, Security $security, PermissionManager $permissionManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::READ_LOG,
            self::READ_LOGS,
        ])) {
            return false;
        }

        // only vote on Log objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::READ_LOG,
        ]) && !$subject instanceof Log) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::READ_LOG:
            case self::READ_LOGS:
                return $this->canReadLog($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadLog(UserInterface $requester)
    {
        // only registered users/apps can read logs
        if (!$requester instanceof UserInterface) {
            return false;
        }

        return $this->permissionManager->checkPermission('log_read', $requester);
    }
}
