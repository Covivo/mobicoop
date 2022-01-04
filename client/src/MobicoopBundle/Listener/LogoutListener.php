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

namespace Mobicoop\Bundle\MobicoopBundle\Listener;

use Mobicoop\Bundle\MobicoopBundle\User\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class LogoutListener implements LogoutSuccessHandlerInterface
{
    protected $router;
    protected $tokenStorage;
    private $session;

    public function __construct(Router $router, TokenStorageInterface $tokenStorage, SessionInterface $session)
    {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->session = $session;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($this->session->get('logoutUrl') && trim($this->session->get('logoutUrl')) !== '') {
            $routeToCall = $this->session->get('logoutUrl');
        } else {
            $routeToCall = $this->tokenStorage->getToken()->getUser() == 'anon.' ?
                $this->router->generate('home_logout', [], UrlGenerator::ABSOLUTE_PATH) :
                $this->router->generate('home', [], UrlGenerator::ABSOLUTE_PATH);
        }


        $this->session->remove('apiToken');
        $this->session->remove('apiRefreshToken');

        return new RedirectResponse($routeToCall);
    }
}
