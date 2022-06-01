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

namespace App\Match\Security;

use App\Auth\Service\AuthManager;
use App\Match\Entity\Mass;
use App\Match\Service\MassImportManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;

class MassVoter extends Voter
{
    public const MASS_CREATE = 'mass_create';
    public const MASS_READ = 'mass_read';
    public const MASS_DELETE = 'mass_delete';
    public const MASS_LIST = 'mass_list';

    private $security;
    private $request;
    private $authManager;
    private $massImportManager;

    public function __construct(RequestStack $requestStack, Security $security, AuthManager $authManager, MassImportManager $massImportManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->authManager = $authManager;
        $this->massImportManager = $massImportManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::MASS_CREATE,
            self::MASS_READ,
            self::MASS_DELETE,
            self::MASS_LIST
            ])) {
            return false;
        }

        // only vote on Mass objects inside this voter
        if (!in_array($attribute, [
            self::MASS_CREATE,
            self::MASS_READ,
            self::MASS_DELETE,
            self::MASS_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Mass)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::MASS_CREATE:
                return $this->canCreateMass();
            case self::MASS_READ:
                if ($mass = $this->massImportManager->getMass($this->request->get('id'))) {
                    return $this->canReadMass($mass);
                }
                return false;
            case self::MASS_DELETE:
                if ($mass = $this->massImportManager->getMass($this->request->get('id'))) {
                    return $this->canDeleteMass($mass);
                }
                return false;
            case self::MASS_LIST:
                return $this->canListMass();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateMass()
    {
        return $this->authManager->isAuthorized(self::MASS_CREATE);
    }

    private function canReadMass(Mass $mass)
    {
        return $this->authManager->isAuthorized(self::MASS_READ, ['mass' => $mass]);
    }

    private function canDeleteMass(Mass $mass)
    {
        return $this->authManager->isAuthorized(self::MASS_DELETE, ['mass' => $mass]);
    }

    private function canListMass()
    {
        return $this->authManager->isAuthorized(self::MASS_LIST);
    }
}
