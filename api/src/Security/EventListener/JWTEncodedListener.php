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

namespace App\Security\EventListener;

use App\User\Entity\User;
use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTEncodedEvent;
use Symfony\Component\Security\Core\Security;

/**
 * Json Web Token Event listener
 * Used to customize the payload of the token, eg. add user id to payload
 */
class JWTEncodedListener
{
    private $entityManager;
    private $jwtEncoder;
    private $userManager;

    public function __construct(EntityManagerInterface $entityManager, JWTEncoderInterface $jwtEncoder, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->jwtEncoder = $jwtEncoder;
        $this->userManager = $userManager;
    }

    /**
     * @param JWTEncodedEvent $event
     */
    public function onJwtEncoded(JWTEncodedEvent $event)
    {
        $token = $event->getJWTString();
        $payload = $this->jwtEncoder->decode($token);
        // for now we check if the token concerns a user by checking the 'admin' index... not very good, to be improved !
        if (isset($payload['admin'])) {
            $user = $this->userManager->getUser($payload['id']);
            $user->setApiToken($token);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
