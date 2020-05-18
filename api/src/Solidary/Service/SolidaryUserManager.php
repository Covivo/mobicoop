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

namespace App\Solidary\Service;

use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUserUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryUserRepository;
use App\User\Repository\UserRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryUserManager
{
    private $entityManager;
    private $eventDispatcher;
    private $solidaryUserRepository;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, SolidaryUserRepository $solidaryUserRepository, UserRepository $userRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->userRepository = $userRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
    }

    public function updateSolidaryUser(SolidaryUser $solidaryUser)
    {
        // We trigger the event
        $event = new SolidaryUserUpdatedEvent($solidaryUser);
        $this->eventDispatcher->dispatch(SolidaryUserUpdatedEvent::NAME, $event);
    }

    /**
     * Get a SolidaryBeneficiary from a User id
     *
     * @param int $id User id
     * @return void
     */
    public function getSolidaryBeneficiary(int $id)
    {
        $solidaryBeneficiary = new SolidaryBeneficiary();

        // Get the User
        $user = $this->userRepository->find($id);

        // Get the SolidaryUser
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_USER);
        }
        $solidaryUser = $user->getSolidaryUser();

        // We check if the SolidaryUser is a Beneficiary
        if (!$solidaryUser->isBeneficiary()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_BENEFICIARY);
        }


        $solidaryBeneficiary->setId($user->getId());
        $solidaryBeneficiary->setEmail($user->getEmail());
        $solidaryBeneficiary->setGivenName($user->getGivenName());
        $solidaryBeneficiary->setFamilyName($user->getFamilyName());
        $solidaryBeneficiary->setBirthDate($user->getBirthDate());
        $solidaryBeneficiary->setUser($user);

        // Home address
        foreach ($user->getAddresses() as $address) {
            if ($address->isHome()) {
                $solidaryBeneficiary->setHomeAddress($address);
            }
        }

        return $solidaryBeneficiary;
    }
}
