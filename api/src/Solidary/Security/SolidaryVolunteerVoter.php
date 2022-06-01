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
use App\Solidary\Entity\SolidaryVolunteer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryVolunteerVoter extends Voter
{
    public const SOLIDARY_VOLUNTEER_REGISTER = 'solidary_volunteer_register';
    public const SOLIDARY_VOLUNTEER_CREATE = 'solidary_volunteer_create';
    public const SOLIDARY_VOLUNTEER_READ = 'solidary_volunteer_read';
    public const SOLIDARY_VOLUNTEER_UPDATE = 'solidary_volunteer_update';
    public const SOLIDARY_VOLUNTEER_DELETE = 'solidary_volunteer_delete';
    public const SOLIDARY_VOLUNTEER_LIST = 'solidary_volunteer_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_VOLUNTEER_REGISTER,
            self::SOLIDARY_VOLUNTEER_CREATE,
            self::SOLIDARY_VOLUNTEER_READ,
            self::SOLIDARY_VOLUNTEER_UPDATE,
            self::SOLIDARY_VOLUNTEER_DELETE,
            self::SOLIDARY_VOLUNTEER_LIST,
        ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_VOLUNTEER_REGISTER,
            self::SOLIDARY_VOLUNTEER_CREATE,
            self::SOLIDARY_VOLUNTEER_READ,
            self::SOLIDARY_VOLUNTEER_UPDATE,
            self::SOLIDARY_VOLUNTEER_DELETE,
            self::SOLIDARY_VOLUNTEER_LIST,
        ]) && !($subject instanceof Paginator)
                && !($subject instanceof SolidaryVolunteer)
            ) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SOLIDARY_VOLUNTEER_REGISTER:
                return $this->canRegisterSolidaryVolunteer();

            case self::SOLIDARY_VOLUNTEER_CREATE:
                return $this->canCreateSolidaryVolunteer();

            case self::SOLIDARY_VOLUNTEER_READ:
                return $this->canReadSolidaryVolunteer($subject);

            case self::SOLIDARY_VOLUNTEER_UPDATE:
                return $this->canUpdateSolidaryVolunteer($subject);

            case self::SOLIDARY_VOLUNTEER_DELETE:
                return $this->canDeleteSolidaryVolunteer($subject);

            case self::SOLIDARY_VOLUNTEER_LIST:
                return $this->canListSolidaryVolunteer();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canRegisterSolidaryVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_REGISTER);
    }

    private function canCreateSolidaryVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_CREATE);
    }

    private function canReadSolidaryVolunteer(SolidaryVolunteer $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_READ, ['solidaryVolunteer' => $solidaryVolunteer]);
    }

    private function canUpdateSolidaryVolunteer(SolidaryVolunteer $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_UPDATE, ['solidaryVolunteer' => $solidaryVolunteer]);
    }

    private function canDeleteSolidaryVolunteer(SolidaryVolunteer $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_DELETE, ['solidaryVolunteer' => $solidaryVolunteer]);
    }

    private function canListSolidaryVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_LIST);
    }
}
