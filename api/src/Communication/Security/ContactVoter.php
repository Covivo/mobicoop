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

namespace App\Communication\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Auth\Service\PermissionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Communication\Entity\Contact;

class ContactVoter extends Voter
{
    const CONTACT_CREATE = 'contact_create';
    
    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CONTACT_CREATE
            ])) {
            return false;
        }

        // only vote on Contact objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::CONTACT_CREATE
            ]) && !($subject instanceof Paginator) && !($subject instanceof Contact)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::CONTACT_CREATE:
                return $this->canCreateContact($requester);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateContact(UserInterface $requester)
    {
        // only registered users/apps can create contact
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('communication_contact', $requester);
    }
}
