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

namespace App\Geography\Security;

use App\Geography\Entity\Territory;
use App\Geography\Service\TerritoryManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class TerritoryVoter extends Voter
{
    public const TERRITORY_CREATE ='territory_create';
    public const TERRITORY_READ = 'territory_read';
    public const TERRITORY_UPDATE = 'territory_update';
    public const TERRITORY_DELETE = 'territory_delete';
    public const TERRITORY_LIST = 'territory_list';
    public const TERRITORY_LINK = 'territory_link';

    private $security;
    private $authManager;
    private $territoryManager;

    public function __construct(Security $security, RequestStack $requestStack, AuthManager $authManager, TerritoryManager $territoryManager)
    {
        $this->security = $security;
        $this->authManager = $authManager;
        $this->territoryManager = $territoryManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::TERRITORY_CREATE,
            self::TERRITORY_READ,
            self::TERRITORY_UPDATE,
            self::TERRITORY_DELETE,
            self::TERRITORY_LIST,
            self::TERRITORY_LINK
            ])) {
            return false;
        }

        if (in_array($attribute, [
            self::TERRITORY_CREATE,
            self::TERRITORY_READ,
            self::TERRITORY_UPDATE,
            self::TERRITORY_DELETE,
            self::TERRITORY_LIST
            ]) && !$subject instanceof Territory && !$subject instanceof Paginator && !is_array($subject)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::TERRITORY_CREATE:
            case self::TERRITORY_LINK:
                return $this->canCreateTerritory();
            case self::TERRITORY_READ:
                if ($territory = $this->territoryManager->getTerritory($this->request->get('id'))) {
                    return $this->canReadTerritory($territory);
                }
                return false;
            case self::TERRITORY_UPDATE:
                if ($territory = $this->territoryManager->getTerritory($this->request->get('id'))) {
                    return $this->canUpdateTerritory($territory);
                }
                return false;
            case self::TERRITORY_DELETE:
                if ($territory = $this->territoryManager->getTerritory($this->request->get('id'))) {
                    return $this->canDeleteTerritory($territory);
                }
                return false;
            case self::TERRITORY_LIST:
                return $this->canListTerritories();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateTerritory()
    {
        return $this->authManager->isAuthorized(self::TERRITORY_CREATE);
    }

    private function canReadTerritory(Territory $territory)
    {
        return $this->authManager->isAuthorized(self::TERRITORY_READ, ['territory' => $territory]);
    }

    private function canUpdateTerritory(Territory $territory)
    {
        return $this->authManager->isAuthorized(self::TERRITORY_UPDATE, ['territory' => $territory]);
    }

    private function canDeleteTerritory(Territory $territory)
    {
        return $this->authManager->isAuthorized(self::TERRITORY_DELETE, ['territory' => $territory]);
    }

    private function canListTerritories()
    {
        return $this->authManager->isAuthorized(self::TERRITORY_LIST);
    }
}
