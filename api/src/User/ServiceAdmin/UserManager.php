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

namespace App\User\ServiceAdmin;

use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\User\Entity\User;
use App\Geography\Entity\Address;
use App\Geography\Repository\TerritoryRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * User manager service for administration.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class UserManager
{
    private $entityManager;
    private $authItemRepository;
    private $territoryRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager The entity manager
     */
    public function __construct(EntityManagerInterface $entityManager, AuthItemRepository $authItemRepository, TerritoryRepository $territoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->authItemRepository = $authItemRepository;
        $this->territoryRepository = $territoryRepository;
    }

    /**
     * Patch a user.
     *
     * @param User $user    The user to update
     * @param array $fields The updated fields
     * @return User         The user updated
     */
    public function patchUser(User $user, array $fields)
    {
        // check if the home address was updated
        if (in_array('homeAddress', array_keys($fields))) {
            // home address updated, we search the original home address
            $homeAddress = null;
            foreach ($user->getAddresses() as $address) {
                if ($address->isHome()) {
                    $homeAddress = $address;
                    break;
                }
            }
            if (!is_null($homeAddress)) {
                // we have to update each field...
                /**
                * @var Address $homeAddress
                */
                $homeAddress->setStreetAddress($user->getHomeAddress()->getStreetAddress());
                $homeAddress->setPostalCode($user->getHomeAddress()->getPostalCode());
                $homeAddress->setAddressLocality($user->getHomeAddress()->getAddressLocality());
                $homeAddress->setAddressCountry($user->getHomeAddress()->getAddressCountry());
                $homeAddress->setLatitude($user->getHomeAddress()->getLatitude());
                $homeAddress->setLongitude($user->getHomeAddress()->getLongitude());
                $homeAddress->setHouseNumber($user->getHomeAddress()->getHouseNumber());
                $homeAddress->setStreetAddress($user->getHomeAddress()->getStreetAddress());
                $homeAddress->setSubLocality($user->getHomeAddress()->getSubLocality());
                $homeAddress->setLocalAdmin($user->getHomeAddress()->getLocalAdmin());
                $homeAddress->setCounty($user->getHomeAddress()->getCounty());
                $homeAddress->setMacroCounty($user->getHomeAddress()->getMacroCounty());
                $homeAddress->setRegion($user->getHomeAddress()->getRegion());
                $homeAddress->setMacroRegion($user->getHomeAddress()->getMacroRegion());
                $homeAddress->setCountryCode($user->getHomeAddress()->getCountryCode());
                $this->entityManager->persist($homeAddress);
                $this->entityManager->flush();
            }
        }

        // check if roles were updated
        if (in_array('rolesTerritory', array_keys($fields))) {
            // remove current roles
            $user->removeUserAuthAssignments();
            // add each role
            foreach ($fields['rolesTerritory'] as $roleTerritory) {
                $userAuthAssignment = new UserAuthAssignment();
                $authItem = $this->authItemRepository->find($roleTerritory['role']);
                $userAuthAssignment->setAuthItem($authItem);
                if (isset($roleTerritory['territory'])) {
                    $territory = $this->territoryRepository->find($roleTerritory['territory']);
                    $userAuthAssignment->setTerritory($territory);
                }
                $user->addUserAuthAssignment($userAuthAssignment);
            }
        }
        
        // persist the user
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        // return the user
        return $user;
    }
}
