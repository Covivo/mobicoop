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
use App\Solidary\Entity\Exposed\Volunteer;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VolunteerVoter extends Voter
{
    const SOLIDARY_VOLUNTEER_CREATE = 'solidary_volunteer_create';
    const SOLIDARY_VOLUNTEER_READ = 'solidary_volunteer_read';
    const SOLIDARY_VOLUNTEER_UPDATE = 'solidary_volunteer_update';
    const SOLIDARY_VOLUNTEER_DELETE = 'solidary_volunteer_delete';
    const SOLIDARY_VOLUNTEER_LIST = 'solidary_volunteer_list';
    const SOLIDARY_VOLUNTEER_REGISTER = 'solidary_volunteer_register';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {

        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_VOLUNTEER_CREATE,
            self::SOLIDARY_VOLUNTEER_READ,
            self::SOLIDARY_VOLUNTEER_UPDATE,
            self::SOLIDARY_VOLUNTEER_DELETE,
            self::SOLIDARY_VOLUNTEER_LIST,
            self::SOLIDARY_VOLUNTEER_REGISTER
            ])) {
            return false;
        }
      
        // only vote on Volunteer objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_VOLUNTEER_CREATE,
            self::SOLIDARY_VOLUNTEER_READ,
            self::SOLIDARY_VOLUNTEER_UPDATE,
            self::SOLIDARY_VOLUNTEER_DELETE,
            self::SOLIDARY_VOLUNTEER_LIST,
            self::SOLIDARY_VOLUNTEER_REGISTER
            ]) && !($subject instanceof Paginator) && !($subject instanceof Volunteer)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::SOLIDARY_VOLUNTEER_CREATE:
                return $this->canCreateVolunteer();
            case self::SOLIDARY_VOLUNTEER_READ:
                return $this->canReadVolunteer($subject);
            case self::SOLIDARY_VOLUNTEER_UPDATE:
                return $this->canUpdateVolunteer($subject);
            case self::SOLIDARY_VOLUNTEER_DELETE:
                return $this->canDeleteVolunteer($subject);
            case self::SOLIDARY_VOLUNTEER_LIST:
                return $this->canListVolunteer();
            case self::SOLIDARY_VOLUNTEER_REGISTER:
                return $this->canRegisterVolunteer($subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_CREATE);
    }

    private function canReadVolunteer(Volunteer $volunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_READ, ['volunteer'=>$volunteer]);
    }

    private function canUpdateVolunteer(Volunteer $volunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_UPDATE, ['volunteer'=>$volunteer]);
    }
    
    private function canDeleteVolunteer(Volunteer $volunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_DELETE, ['volunteer'=>$volunteer]);
    }
    
    private function canListVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_LIST);
    }

    private function canRegisterVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_REGISTER);
    }
}
