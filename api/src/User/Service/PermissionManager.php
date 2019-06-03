<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\User\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\User\Entity\User;
use App\Geography\Entity\Territory;
use App\Right\Repository\RightRepository;

/**
 * Permission manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PermissionManager
{
    private $entityManager;
    private $logger;
    private $rightRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, RightRepository $rightRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->rightRepository = $rightRepository;
    }

    /**
     * Check if a user has a permission on an action, eventually on a given territory
     *
     * @param User $user
     * @param string $action
     * @param Territory|null $territory
     * @return void
     */
    public function userHasPermission(User $user, string $action, ?Territory $territory=null): bool
    {
        // we first check if the user is seated on the iron throne
        if (in_array('ROLE_SUPER_ADMIN',$user->getRoles())) {
            // King of the Andals and the First Men, Lord of the Seven Kingdoms, and Protector of the Realm
            return true;
        }

        // we check if a role of the user has the right to do the action
        foreach ($user->getUserRoles() as $userRole) {
            if (is_null($userRole->getTerritory()) || $userRole->getTerritory() == $territory) {
                foreach ($userRole->getRights() as $right) {
                    if ($right->getName() == $action) {
                        return true;
                    } else {
                        foreach ($this->rightRepository->findChildren($right) as $child) {
                            if ($child->getName() == $action) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        // we check if a the user has the specific right to do the action
        foreach ($user->getUserRights() as $userRight) {
            if (is_null($userRight->getTerritory()) || $userRight->getTerritory() == $territory) {
                foreach ($userRight->getRights() as $right) {
                    if ($right->getName() == $action) {
                        return true;
                    } else {
                        foreach ($this->rightRepository->findChildren($right) as $child) {
                            if ($child->getName() == $action) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
