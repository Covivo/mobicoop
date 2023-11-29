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
 */

namespace App\Security\EventListener;

use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Json Web Token Event listener
 * Used to customize the payload of the token, eg. add user id to payload.
 */
class JWTCreatedListener
{
    private $authManager;
    private $security;
    private $userManager;
    private $request;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, AuthManager $authManager, Security $security, userManager $userManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
        $this->security = $security;
        $this->userManager = $userManager;
        $this->_em = $em;
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();

        /**
         * @var App|User $user
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
            if ($this->request->get('mobile')) {
                $user->setMobile(true);
            }

            switch (true) {
                // Delegate authentication use case
                case !is_null($user) && !is_null($this->security->getUser()) && $user->getId() != $this->security->getUser()->getId():
                    $this->userManager->createAuthenticationDelegation($this->security->getUser(), $user);
                    $payload['delegateAuth'] = true;

                    break;

                default:
                    $this->userManager->updateActivity($user);

                    break;
            }
        }
        $event->setData($payload);

        $header = $event->getHeader();
        $header['cty'] = 'JWT';
        $event->setHeader($header);
    }
}
