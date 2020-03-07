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

use App\Auth\Service\PermissionManager;
use App\User\Entity\User;
use App\App\Entity\App;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Json Web Token Event listener
 * Used to customize the payload of the token, eg. add user id to payload
 */
class JWTCreatedListener
{

    /**
     * @var RequestStack
     */
    private $requestStack;
    private $permissionManager;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack, PermissionManager $permissionManager)
    {
        $this->requestStack = $requestStack;
        $this->permissionManager = $permissionManager;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $payload = $event->getData();

        /**
         * @var User|App $user
         */
        $user = $event->getUser();
        $payload['id'] = $user->getId();
        if ($user instanceof User) {
            $payload['permissions'] = $this->permissionManager->getUserPermissions($user);
        }
        $event->setData($payload);
        $header = $event->getHeader();
        $header['cty'] = 'JWT';

        $event->setHeader($header);
    }
}
