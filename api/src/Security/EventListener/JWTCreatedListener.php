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
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\Security\Core\Security;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Json Web Token Event listener
 * Used to customize the payload of the token, eg. add user id to payload
 */
class JWTCreatedListener
{
    private $authManager;
    private $security;
    private $userManager;
    private $request;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, Security $security, userManager $userManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
        $this->security = $security;
        $this->userManager = $userManager;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        /**
         * @var User|App $user
         */
        $user = $event->getUser();
        $payload['id'] = $user->getId();
        if ($user instanceof User) {
            if (is_null($this->security->getUser())) {
                // anonymous connection => maybe a refresh request
                // we set the authManager user to the user related with the refreshed token
                $this->authManager->setUser($user);
            }
            $payload['admin'] = $this->authManager->isAuthorized('access_admin');
            // TODO : when log system is on, send here an event for "logged user", and treat the mobile in this event
            // for now we set the mobile here
            if ($this->request->get("mobile")) {
                $user->setMobile(true);
            }
            $this->userManager->updateActivity($user);
        }
        $event->setData($payload);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
    }
}
