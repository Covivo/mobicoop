<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\EventSubscriber;

use App\User\Entity\User;
use App\User\Service\UserManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class RequestSubscriber implements EventSubscriberInterface
{
    private $_security;
    private $_request;
    private $_userManager;

    public function __construct(Security $security, RequestStack $requestStack, UserManager $userManager)
    {
        $this->_security = $security;
        $this->_request = $requestStack->getCurrentRequest();
        $this->_userManager = $userManager;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ((!is_null($this->_security->getUser())) && ($this->_security->getUser() instanceof User)) {
            $bearerToken = $this->_extractBearerToken($this->_request->headers->get('Authorization'));
            if (!is_null($bearerToken)) {
                $decodedBearerToken = $this->_jwtDecode($bearerToken);
                if (
                    !isset($decodedBearerToken['delegateAuth'])
                    || isset($decodedBearerToken['delegateAuth']) && false === $decodedBearerToken['delegateAuth']
                ) {
                    $this->_userManager->updateActivity($this->_security->getUser());
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    private function _jwtDecode(string $jwt)
    {
        $jwtParts = explode('.', $jwt);
        $decodedPayload = base64_decode(str_replace(['-', '_'], ['+', '/'], $jwtParts[1]));

        return json_decode($decodedPayload, true);
    }

    private function _extractBearerToken(?string $authChain): ?string
    {
        if (!is_null($authChain) && preg_match('/^Bearer\s+(.+)/i', $authChain, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
