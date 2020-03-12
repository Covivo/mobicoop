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
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

/**
 * Json Web Token Event listener
 * Used to customize the payload of the token, eg. add user id to payload
 */
class JWTCreatedListener
{
    private $authManager;
    private $entityManager;

    public function __construct(AuthManager $authManager, EntityManagerInterface $entityManager)
    {
        $this->authManager = $authManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        $user = $event->getUser();
        if ($user instanceof App || $user instanceof User) {
            /**
             * @var User|App $user
             */
            
            $payload['id'] = $user->getId();
            if ($user instanceof User) {
                $payload['admin'] = $this->authManager->isAuthorized('access_admin');
            }
            $event->setData($payload);
        }
        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
    }
}
