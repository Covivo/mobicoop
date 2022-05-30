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

namespace App\Auth\ServiceAdmin;

use App\Auth\Entity\AuthItem;
use App\Auth\Service\AuthManager as ServiceAuthManager;
use App\Geography\Entity\Territory;
use App\User\Entity\User;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Auth manager service in administration context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AuthManager
{
    private $entityManager;
    private $authManager;
    private $authItemRepository;

    const GRANTABLE_ROLES =  [
        AuthItem::ROLE_SUPER_ADMIN => [
            AuthItem::ROLE_SUPER_ADMIN,
            AuthItem::ROLE_ADMIN,
            AuthItem::ROLE_USER_REGISTERED_FULL,
            AuthItem::ROLE_MASS_MATCH
        ],
        AuthItem::ROLE_ADMIN => [
            AuthItem::ROLE_ADMIN,
            AuthItem::ROLE_USER_REGISTERED_FULL
        ],
        AuthItem::ROLE_SOLIDARY_MANAGER => [
            AuthItem::ROLE_USER_REGISTERED_FULL,
            AuthItem::ROLE_SOLIDARY_VOLUNTEER,
            AuthItem::ROLE_SOLIDARY_BENEFICIARY
        ],
    ];

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, ServiceAuthManager $authManager, AuthItemRepository $authItemRepository)
    {
        $this->entityManager = $entityManager;
        $this->authManager = $authManager;
        $this->authItemRepository = $authItemRepository;
    }

    /**
     * Get an authItem from its id
     *
     * @param integer $id       The id
     * @return AuthItem|null    The authItem or null if not found
     */
    public function getAuthItem(int $id): ?AuthItem
    {
        return $this->authItemRepository->find($id);
    }

    /**
     * Get grantable roles for the current user
     *
     * @return AuthItem|null
     */
    public function getGrantable(): ?AuthItem
    {
        $authItems = $this->authManager->getAuthItems(AuthItem::TYPE_ROLE, true);
        $rolesGranted = [];
        foreach ($authItems as $authItem) {
            if (array_key_exists($authItem['id']->getId(), self::GRANTABLE_ROLES)) {
                $rolesGranted = array_unique(array_merge($rolesGranted, self::GRANTABLE_ROLES[$authItem['id']->getId()]));
            }
        }
        return $rolesGranted;
    }

    /**
     * Grant an auth item to a user, eventually on a given territory (if not already granted)
     *
     * @param User $user            The user
     * @param AuthItem $authItem    The auth item
     * @param Territory $territory  The territory
     * @param bool $flush           Flush immediately
     * @return void
     */
    public function grant(User $user, AuthItem $authItem, ?Territory $territory=null, bool $flush = true): void
    {
        // check if the auth item already exists
        $granted = false;
        foreach ($user->getUserAuthAssignments() as $userAuthAssignment) {
            /**
             * @var UserAuthAssignment $userAuthAssignment
             */
            if ($userAuthAssignment->getAuthItem()->getId() === $authItem->getId()) {
                // item already granted, check territory
                if (is_null($territory) || (!is_null($userAuthAssignment->getTerritory()) && $userAuthAssignment->getTerritory()->getId() === $territory->getId())) {
                    $granted = true;
                    break;
                }
            }
        }
        if (!$granted) {
            // auth item not already granted
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $userAuthAssignment->setUser($user);
            $userAuthAssignment->setTerritory($territory);
            $this->entityManager->persist($userAuthAssignment);
            if ($flush) {
                $this->entityManager->flush();
            }
        }
    }

    /**
     * Revoke an auth item for a user, eventually on a given territory
     *
     * @param User $user            The user
     * @param AuthItem $authItem    The auth item
     * @param Territory $territory  The territory
     * @param bool $flush           Flush immediately
     * @return void
     */
    public function revoke(User $user, AuthItem $authItem, ?Territory $territory, bool $flush = true): void
    {
        foreach ($user->getUserAuthAssignments() as $userAuthAssignment) {
            /**
             * @var UserAuthAssignment $userAuthAssignment
             */
            if ($userAuthAssignment->getAuthItem()->getId() === $authItem->getId() && (is_null($territory) || $userAuthAssignment->getTerritory() === $territory)) {
                $this->entityManager->remove($userAuthAssignment);
                if ($flush) {
                    $this->entityManager->flush();
                }
            }
        }
    }
}
