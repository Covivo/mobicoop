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

namespace App\Solidary\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Solidary\Entity\Structure;
use App\Solidary\Entity\StructureProof;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class StructureVoter extends Voter
{
    public const STRUCTURE_CREATE = 'structure_create';
    public const STRUCTURE_READ = 'structure_read';
    public const STRUCTURE_UPDATE = 'structure_update';
    public const STRUCTURE_DELETE = 'structure_delete';
    public const STRUCTURE_LIST = 'structure_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::STRUCTURE_CREATE,
            self::STRUCTURE_READ,
            self::STRUCTURE_UPDATE,
            self::STRUCTURE_DELETE,
            self::STRUCTURE_LIST,
            ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::STRUCTURE_CREATE,
            self::STRUCTURE_READ,
            self::STRUCTURE_UPDATE,
            self::STRUCTURE_DELETE,
            self::STRUCTURE_LIST,
            ]) && !($subject instanceof Paginator) && !($subject instanceof Structure || $subject instanceof StructureProof)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::STRUCTURE_CREATE:
                return $this->canCreateStructure();
            case self::STRUCTURE_READ:
                return $this->canReadStructure();
            case self::STRUCTURE_UPDATE:
                return $this->canUpdateStructure();
            case self::STRUCTURE_DELETE:
                return $this->canDeleteStructure();
            case self::STRUCTURE_LIST:
                return $this->canListStructure();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_CREATE);
    }

    private function canReadStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_READ);
    }

    private function canUpdateStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_UPDATE);
    }

    private function canDeleteStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_DELETE);
    }

    private function canListStructure()
    {
        return $this->authManager->isAuthorized(self::STRUCTURE_LIST);
    }
}
