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
use App\Solidary\Entity\SolidaryUser;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Solidary\Entity\SolidaryVolunteer;
use App\Solidary\Repository\SolidaryUserRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class SolidaryVolunteerVoter extends Voter
{
    const ADMIN_SOLIDARY_VOLUNTEER_CREATE = 'admin_solidary_volunteer_create';
    const ADMIN_SOLIDARY_VOLUNTEER_READ = 'admin_solidary_volunteer_read';
    const ADMIN_SOLIDARY_VOLUNTEER_UPDATE = 'admin_solidary_volunteer_update';
    const ADMIN_SOLIDARY_VOLUNTEER_DELETE = 'admin_solidary_volunteer_delete';
    const ADMIN_SOLIDARY_VOLUNTEER_LIST = 'admin_solidary_volunteer_list';
    const SOLIDARY_VOLUNTEER_CREATE = 'solidary_volunteer_create';
    const SOLIDARY_VOLUNTEER_READ = 'solidary_volunteer_read';
    const SOLIDARY_VOLUNTEER_UPDATE = 'solidary_volunteer_update';
    const SOLIDARY_VOLUNTEER_DELETE = 'solidary_volunteer_delete';
    const SOLIDARY_VOLUNTEER_LIST = 'solidary_volunteer_list';

    private $authManager;
    private $solidaryUserRepository;
    private $request;
 
    public function __construct(RequestStack $requestStack, AuthManager $authManager, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->authManager = $authManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_VOLUNTEER_CREATE,
            self::ADMIN_SOLIDARY_VOLUNTEER_READ,
            self::ADMIN_SOLIDARY_VOLUNTEER_UPDATE,
            self::ADMIN_SOLIDARY_VOLUNTEER_DELETE,
            self::ADMIN_SOLIDARY_VOLUNTEER_LIST
            ])) {
            return false;
        }

        // only vote on Structure objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_VOLUNTEER_CREATE,
            self::ADMIN_SOLIDARY_VOLUNTEER_READ,
            self::ADMIN_SOLIDARY_VOLUNTEER_UPDATE,
            self::ADMIN_SOLIDARY_VOLUNTEER_DELETE,
            self::ADMIN_SOLIDARY_VOLUNTEER_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof SolidaryVolunteer)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (is_null($subject)) {
            $subject = $this->solidaryUserRepository->find($this->request->get('id'));
        }
        switch ($attribute) {
            case self::ADMIN_SOLIDARY_VOLUNTEER_CREATE:
                return $this->canCreateSolidaryVolunteer();
            case self::ADMIN_SOLIDARY_VOLUNTEER_READ:
                return $this->canReadSolidaryVolunteer($subject);
            case self::ADMIN_SOLIDARY_VOLUNTEER_UPDATE:
                return $this->canUpdateSolidaryVolunteer($subject);
            case self::ADMIN_SOLIDARY_VOLUNTEER_DELETE:
                return $this->canDeleteSolidaryVolunteer($subject);
            case self::ADMIN_SOLIDARY_VOLUNTEER_LIST:
                return $this->canListSolidaryVolunteer();
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidaryVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_CREATE);
    }

    private function canReadSolidaryVolunteer(SolidaryUser $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_READ, ['solidaryVolunteer'=>$solidaryVolunteer]);
    }

    private function canUpdateSolidaryVolunteer(SolidaryUser $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_UPDATE, ['solidaryVolunteer'=>$solidaryVolunteer]);
    }

    private function canDeleteSolidaryVolunteer(SolidaryUser $solidaryVolunteer)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_DELETE, ['solidaryVolunteer'=>$solidaryVolunteer]);
    }

    private function canListSolidaryVolunteer()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_VOLUNTEER_LIST);
    }
}
