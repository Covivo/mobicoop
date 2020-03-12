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

namespace App\Match\Service;

use App\Geography\Entity\Address;
use App\Match\Entity\Mass;
use App\Match\Entity\MassPerson;
use App\Match\Repository\MassPersonRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Right\Repository\RoleRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Mass compute manager.
 *
 * This service compute all Masses data.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassMigrateManager
{
    private $massPersonRepository;
    private $entityManager;
    private $encoder;
    private $roleRepository;
    private $userRepository;
    private $params;

    public function __construct(MassPersonRepository $massPersonRepository, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder, RoleRepository $roleRepository, UserRepository $userRepository, array $params)
    {
        $this->massPersonRepository = $massPersonRepository;
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->params = $params;
    }

    /**
     * Migrate the mass's Users (MassPerson to User)
     *
     * @param Mass $mass    The Mass owning the MassPersons to migrate
     * @return Mass
     */
    public function migrate(Mass $mass)
    {
        $migratedUsers = [];

        // First, we set the status of the Mass at Migrating
        // TO DO : Save the migrating date
        $mass->setStatus(Mass::STATUS_MIGRATING);
        $this->entityManager->persist($mass);
        $this->entityManager->flush();


        // Then we get the Mass persons related the this mass
        $massPersons = $this->massPersonRepository->findAllByMass($mass);

        // Then for each person we're creating a User (with a Role and an Address)

        /**
         * @var MassPerson $massPerson
         */
        foreach ($massPersons as $massPerson) {

            // We check if this user already exists
            $userFound = $this->userRepository->findOneBy(["email"=>$massPerson->getEmail()]);

            if (!is_null($userFound)) {
                // This MassPerson has already an existing account
                // We're returning the founded User
                $userFound->setAlreadyRegistered(true);
                $migratedUsers[] = $userFound;
            } else {

                // We don't know this MassPerson. We're creating the User.

                $user = new User();
                $user->setGivenName($massPerson->getGivenName());
                $user->setFamilyName($massPerson->getFamilyName());
                $user->setEmail($massPerson->getEmail());
                // To do : Add gender to csv/xml file
                $user->setGender(User::GENDER_OTHER);
                // To do : Birthdate ?

                $user->setPhoneDisplay(1);
                $user->setSmoke($this->params['smoke']);
                $user->setMusic($this->params['music']);
                $user->setChat($this->params['chat']);
                // To do : Dynamic Language
                $user->setLanguage('fr_FR');

                // Set an encrypted password
                $password = $this->randomPassword();
                $user->setPassword($this->encoder->encodePassword($user, $password));

                // auto valid the registration
                $user->setValidatedDate(new \DateTime());

                // We give him a fully registrated role
                // default role : user registered full
                $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL);
                $userRole = new UserRole();
                $userRole->setRole($role);
                $user->addUserRole($userRole);

                $this->entityManager->persist($user);

                $migratedUsers[] = $user; // For the return
                
                // The home address of the user
                $personalAddress = clone $massPerson->getPersonalAddress();
                $personalAddress->setUser($user);
                $personalAddress->setHome(true);

                $this->entityManager->persist($personalAddress);

                // To do : Trigger an event to send a email
            }
        }
        $this->entityManager->flush();

        // Finally, we set status of the Mass at Migrated
        // First, we set the status of the Mass at Migrating
        // TO DO : Save the migrated date
        $mass->setStatus(Mass::STATUS_MIGRATED);
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        $mass->setMigratedUsers($migratedUsers);

        return $mass;
    }

    private function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
