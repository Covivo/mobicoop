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
 */

namespace App\User\Service;

use App\User\Entity\PushToken;
use App\User\Repository\PushTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Push token manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PushTokenManager
{
    private $entityManager;
    private $pushTokenRepository;

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, PushTokenRepository $pushTokenRepository)
    {
        $this->entityManager = $entityManager;
        $this->pushTokenRepository = $pushTokenRepository;
    }

    /**
     * Create a new token if it doesn't exist yet.
     *
     * @param PushToken $pushToken The push token
     *
     * @return PushToken The push token created or retrieved
     */
    public function createPushToken(PushToken $pushToken): PushToken
    {
        if ($existingPushToken = $this->pushTokenRepository->findOneBy(['token' => $pushToken->getToken(), 'user' => $pushToken->getUser()])) {
            return $existingPushToken;
        }
        $this->entityManager->persist($pushToken);
        $this->entityManager->flush();

        return $pushToken;
    }

    /**
     * Delete a token if it exists.
     *
     * @param PushToken $pushToken The push token to delete
     */
    public function deletePushToken(PushToken $pushToken)
    {
        $this->entityManager->remove($pushToken);
        $this->entityManager->flush();

        return $pushToken;
    }
}
