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

namespace App\Solidary\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryContact;
use App\Solidary\Entity\SolidaryFormalRequest;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Entity\SolidarySolution;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryVoter extends Voter
{
    public const SOLIDARY_CREATE = 'solidary_create';
    public const SOLIDARY_READ = 'solidary_read';
    public const SOLIDARY_UPDATE = 'solidary_update';
    public const SOLIDARY_DELETE = 'solidary_delete';
    public const SOLIDARY_LIST = 'solidary_list';
    public const SOLIDARY_LIST_SELF = 'solidary_list_self';
    public const SOLIDARY_CONTACT = 'solidary_contact';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_CREATE,
            self::SOLIDARY_READ,
            self::SOLIDARY_UPDATE,
            self::SOLIDARY_DELETE,
            self::SOLIDARY_LIST,
            self::SOLIDARY_LIST_SELF,
            self::SOLIDARY_CONTACT,
        ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_CREATE,
            self::SOLIDARY_READ,
            self::SOLIDARY_UPDATE,
            self::SOLIDARY_DELETE,
            self::SOLIDARY_LIST,
            self::SOLIDARY_LIST_SELF,
            self::SOLIDARY_CONTACT,
        ]) && !($subject instanceof Paginator)
                && !($subject instanceof Solidary)
                && !($subject instanceof SolidarySolution)
                && !($subject instanceof SolidarySearch)
                && !($subject instanceof SolidaryContact)
                && !($subject instanceof SolidaryFormalRequest)
            ) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SOLIDARY_CREATE:
                return $this->canCreateSolidary();

            case self::SOLIDARY_READ:
                ($subject instanceof Solidary) ? $solidary = $subject : $solidary = $subject->getSolidary();

                return $this->canReadSolidary($solidary);

            case self::SOLIDARY_UPDATE:
                ($subject instanceof Solidary) ? $solidary = $subject : $solidary = $subject->getSolidaryMatching()->getSolidary();

                return $this->canUpdateSolidary($solidary);

            case self::SOLIDARY_DELETE:
                return $this->canDeleteSolidary($subject);

            case self::SOLIDARY_LIST:
                return $this->canListSolidary();

            case self::SOLIDARY_LIST_SELF:
                return $this->canListSolidarySelf();

            case self::SOLIDARY_CONTACT:
                return $this->canUpdateSolidary($subject->getSolidarySolution()->getSolidary());
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_CREATE);
    }

    private function canReadSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_READ, ['solidary' => $solidary]);
    }

    private function canUpdateSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_UPDATE, ['solidary' => $solidary]);
    }

    private function canDeleteSolidary(Solidary $solidary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_DELETE, ['solidary' => $solidary]);
    }

    private function canListSolidary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_LIST);
    }

    private function canListSolidarySelf()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_LIST_SELF);
    }
}
