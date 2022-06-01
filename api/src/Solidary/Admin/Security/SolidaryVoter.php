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

namespace App\Solidary\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Solidary\Entity\Solidary;
use Symfony\Component\HttpFoundation\RequestStack;

class SolidaryVoter extends Voter
{
    public const ADMIN_SOLIDARY_CREATE = 'admin_solidary_create';
    public const ADMIN_SOLIDARY_READ = 'admin_solidary_read';
    public const ADMIN_SOLIDARY_UPDATE = 'admin_solidary_update';
    public const ADMIN_SOLIDARY_DELETE = 'admin_solidary_delete';
    public const ADMIN_SOLIDARY_LIST = 'admin_solidary_list';
    public const SOLIDARY_CREATE = 'solidary_create';
    public const SOLIDARY_READ = 'solidary_read';
    public const SOLIDARY_UPDATE = 'solidary_update';
    public const SOLIDARY_DELETE = 'solidary_delete';
    public const SOLIDARY_LIST = 'solidary_list';

    private $authManager;
    private $request;

    public function __construct(RequestStack $requestStack, AuthManager $authManager)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_CREATE,
            self::ADMIN_SOLIDARY_READ,
            self::ADMIN_SOLIDARY_UPDATE,
            self::ADMIN_SOLIDARY_DELETE,
            self::ADMIN_SOLIDARY_LIST
            ])) {
            return false;
        }

        // only vote on Structure objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_CREATE,
            self::ADMIN_SOLIDARY_READ,
            self::ADMIN_SOLIDARY_UPDATE,
            self::ADMIN_SOLIDARY_DELETE,
            self::ADMIN_SOLIDARY_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Solidary)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_SOLIDARY_CREATE:
                return $this->canCreateSolidary();
            case self::ADMIN_SOLIDARY_READ:
                return $this->canReadSolidary($subject);
            case self::ADMIN_SOLIDARY_UPDATE:
                return $this->canUpdateSolidary($subject);
            case self::ADMIN_SOLIDARY_DELETE:
                return $this->canDeleteSolidary($subject);
            case self::ADMIN_SOLIDARY_LIST:
                return $this->canListSolidary();
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_CREATE);
    }

    private function canReadSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_READ, ['solidary'=>$solidary]);
    }

    private function canUpdateSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_UPDATE, ['solidary'=>$solidary]);
    }

    private function canDeleteSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_DELETE, ['solidary'=>$solidary]);
    }

    private function canListSolidary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_LIST);
    }
}
