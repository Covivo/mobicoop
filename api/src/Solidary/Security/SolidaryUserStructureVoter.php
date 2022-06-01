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
use App\Solidary\Entity\SolidaryUserStructure;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUserStructureVoter extends Voter
{
    public const SOLIDARY_USER_STRUCTURE_CREATE = 'solidary_user_structure_create';
    public const SOLIDARY_USER_STRUCTURE_READ = 'solidary_user_structure_read';
    public const SOLIDARY_USER_STRUCTURE_UPDATE = 'solidary_user_structure_update';
    public const SOLIDARY_USER_STRUCTURE_DELETE = 'solidary_user_structure_delete';
    public const SOLIDARY_USER_STRUCTURE_LIST = 'solidary_user_structure_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_USER_STRUCTURE_CREATE,
            self::SOLIDARY_USER_STRUCTURE_READ,
            self::SOLIDARY_USER_STRUCTURE_UPDATE,
            self::SOLIDARY_USER_STRUCTURE_DELETE,
            self::SOLIDARY_USER_STRUCTURE_LIST,
            ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_USER_STRUCTURE_CREATE,
            self::SOLIDARY_USER_STRUCTURE_READ,
            self::SOLIDARY_USER_STRUCTURE_UPDATE,
            self::SOLIDARY_USER_STRUCTURE_DELETE,
            self::SOLIDARY_USER_STRUCTURE_LIST,
            ]) && !($subject instanceof Paginator) && !($subject instanceof SolidaryUserStructure)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SOLIDARY_USER_STRUCTURE_CREATE:
                return $this->canCreateSolidaryUserStructure();
            case self::SOLIDARY_USER_STRUCTURE_READ:
                return $this->canReadSolidaryUserStructure($subject);
            case self::SOLIDARY_USER_STRUCTURE_UPDATE:
                return $this->canUpdateSolidaryUserStructure($subject);
            case self::SOLIDARY_USER_STRUCTURE_DELETE:
                return $this->canDeleteSolidaryUserStructure($subject);
            case self::SOLIDARY_USER_STRUCTURE_LIST:
                return $this->canListSolidaryUserStructure();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidaryUserStructure()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_USER_STRUCTURE_CREATE);
    }

    private function canReadSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_USER_STRUCTURE_READ, ['solidaryUserStructure'=>$solidaryUserStructure]);
    }

    private function canUpdateSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_USER_STRUCTURE_UPDATE, ['solidaryUserStructure'=>$solidaryUserStructure]);
    }

    private function canDeleteSolidaryUserStructure(SolidaryUserStructure $solidaryUserStructure)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_USER_STRUCTURE_DELETE, ['solidaryUserStructure'=>$solidaryUserStructure]);
    }

    private function canListSolidaryUserStructure()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_USER_STRUCTURE_LIST);
    }
}
