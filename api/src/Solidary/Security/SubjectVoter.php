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
use App\Solidary\Entity\Subject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SubjectVoter extends Voter
{
    const SUBJECT_CREATE = 'subject_create';
    const SUBJECT_READ = 'subject_read';
    const SUBJECT_UPDATE = 'subject_update';
    const SUBJECT_DELETE = 'subject_delete';
    const SUBJECT_LIST = 'subject_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SUBJECT_CREATE,
            self::SUBJECT_READ,
            self::SUBJECT_UPDATE,
            self::SUBJECT_DELETE,
            self::SUBJECT_LIST,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SUBJECT_CREATE,
            self::SUBJECT_READ,
            self::SUBJECT_UPDATE,
            self::SUBJECT_DELETE,
            self::SUBJECT_LIST,
            ]) && !($subject instanceof Paginator) && !($subject instanceof Subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SUBJECT_CREATE:
                return $this->canCreateSubject();
            case self::SUBJECT_READ:
                return $this->canReadSubject($subject);
            case self::SUBJECT_UPDATE:
                return $this->canUpdateSubject($subject);
            case self::SUBJECT_DELETE:
                return $this->canDeleteSubject($subject);
            case self::SUBJECT_LIST:
                return $this->canListSubject();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSubject()
    {
        return $this->authManager->isAuthorized(self::SUBJECT_CREATE);
    }

    private function canReadSubject(Subject $subject)
    {
        return $this->authManager->isAuthorized(self::SUBJECT_READ, ['subject'=>$subject]);
    }

    private function canUpdateSubject(Subject $subject)
    {
        return $this->authManager->isAuthorized(self::SUBJECT_UPDATE, ['subject'=>$subject]);
    }
    
    private function canDeleteSubject(Subject $subject)
    {
        return $this->authManager->isAuthorized(self::SUBJECT_DELETE, ['subject'=>$subject]);
    }
    
    private function canListSubject()
    {
        return $this->authManager->isAuthorized(self::SUBJECT_LIST);
    }
}
