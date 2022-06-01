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

namespace App\Import\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Event\Service\EventManager;
use App\Geography\Entity\Address;
use App\Import\Entity\CommunityImport;
use App\Import\Entity\EventImport;
use App\Import\Entity\RelayPointImport;
use App\Import\Entity\UserImport;
use Symfony\Component\HttpFoundation\RequestStack;

class ImportVoter extends Voter
{
    public const IMPORT_CREATE = 'import_create';

    private $authManager;
    private $requestStack;
    private $eventManager;

    public function __construct(AuthManager $authManager, RequestStack $requestStack, EventManager $eventManager)
    {
        $this->authManager = $authManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->eventManager = $eventManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::IMPORT_CREATE
            ])) {
            return false;
        }

        // only vote on related objects inside this voter
        if (!in_array($attribute, [
            self::IMPORT_CREATE
            ]) &&
            !($subject instanceof CommunityImport) &&
            !($subject instanceof EventImport) &&
            !($subject instanceof RelayPointImport) &&
            !($subject instanceof UserImport) &&
            !($subject instanceof Address)
            ) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::IMPORT_CREATE:
                return $this->canCreateImport();
                break;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateImport()
    {
        return $this->authManager->isAuthorized(self::IMPORT_CREATE);
    }
}
