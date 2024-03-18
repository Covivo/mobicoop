<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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
 */

namespace App\User\Service;

use App\User\Repository\UserRepository;
use App\User\Ressource\UserUnderEighteen;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Unser under Eighteen manager service.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserUnderEighteenManager
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function getUserUnderEighteenByToken(string $token)
    {
        $user = $this->userRepository->findOneBy(['parentalConsentToken' => $token]);
        if (is_null($user)) {
            throw new \LogicException('User not found', 1);
        }

        $userUnderEighteen = new UserUnderEighteen();
        $userUnderEighteen->setGivenName($user->getGivenName());
        $userUnderEighteen->setFamilyName($user->getFamilyName());

        return $userUnderEighteen;
    }

    public function giveParentalConsent(UserUnderEighteen $userUnderEighteen)
    {
        $user = $this->userRepository->findOneBy(['parentalConsentUuid' => $userUnderEighteen->getUuid(), 'parentalConsentToken' => $userUnderEighteen->getToken()]);
        if (is_null($user)) {
            throw new \LogicException('User not found, the parental consent failed', 1);
        }
        $user->setParentalConsentDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $userUnderEighteen;
    }
}
