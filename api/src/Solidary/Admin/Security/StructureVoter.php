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
use App\Solidary\Entity\Structure;
use Symfony\Component\HttpFoundation\RequestStack;

class StructureVoter extends Voter
{
    const ADMIN_STRUCTURE_CREATE = 'admin_structure_create';
    const ADMIN_STRUCTURE_READ = 'admin_structure_read';
    const ADMIN_STRUCTURE_UPDATE = 'admin_structure_update';
    const ADMIN_STRUCTURE_DELETE = 'admin_structure_delete';
    const ADMIN_STRUCTURE_LIST = 'admin_structure_list';
    const STRUCTURE_CREATE = 'structure_create';
    const STRUCTURE_READ = 'structure_read';
    const STRUCTURE_UPDATE = 'structure_update';
    const STRUCTURE_DELETE = 'structure_delete';
    const STRUCTURE_LIST = 'structure_list';

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
            self::ADMIN_STRUCTURE_CREATE,
            self::ADMIN_STRUCTURE_READ,
            self::ADMIN_STRUCTURE_UPDATE,
            self::ADMIN_STRUCTURE_DELETE,
            self::ADMIN_STRUCTURE_LIST
            ])) {
            return false;
        }

        // only vote on Structure objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_STRUCTURE_CREATE,
            self::ADMIN_STRUCTURE_READ,
            self::ADMIN_STRUCTURE_UPDATE,
            self::ADMIN_STRUCTURE_DELETE,
            self::ADMIN_STRUCTURE_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Structure)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_STRUCTURE_CREATE:
                return $this->canCreateStructure();
            case self::ADMIN_STRUCTURE_READ:
                return $this->canReadStructure($subject);
            case self::ADMIN_STRUCTURE_UPDATE:
                return $this->canUpdateStructure($subject);
            case self::ADMIN_STRUCTURE_DELETE:
                return $this->canDeleteStructure($subject);
            case self::ADMIN_STRUCTURE_LIST:
                return $this->canListStructure();
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_CREATE);
    }

    private function canReadStructure(Structure $structure)
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_READ, ['structure'=>$structure]);
    }

    private function canUpdateStructure(Structure $structure)
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_UPDATE, ['structure'=>$structure]);
    }

    private function canDeleteStructure(Structure $structure)
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_DELETE, ['structure'=>$structure]);
    }

    private function canListStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_LIST);
    }
}
