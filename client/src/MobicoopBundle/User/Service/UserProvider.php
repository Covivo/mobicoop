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

namespace Mobicoop\Bundle\MobicoopBundle\User\Service;

use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserProvider implements UserProviderInterface
{
    const USER_LOGIN_ROUTE = "user_login";

    private $dataProvider;
    private $router;
    private $translator;
    private $request;
    private $user;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider, RouterInterface $router, TranslatorInterface $translator, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(User::class);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        if ($this->request->get('_route') == self::USER_LOGIN_ROUTE && $this->request->get('email') && $this->request->get('password')) {
            // we want to login, we set the credentials for the dataProvider
            $this->dataProvider->setUsername($this->request->get('email'));
            $this->dataProvider->setPassword($this->request->get('password'));
            // we set the dataProvider to private => will discard the current JWT token
            $this->dataProvider->setPrivate(true);
        }
        return $this->fetchUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }
        $username = $user->getUsername();

        return $this->fetchUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return User::class === $class;
    }

    /**
     * {@inheritdoc}
     */
    private function fetchUser($username)
    {
        $response = $this->dataProvider->getSpecialCollection("me");
        if ($response->getCode() == 200) {
            $userData = $response->getValue();

            if (is_array($userData->getMember()) && count($userData->getMember())==1) {
                return $userData->getMember()[0];
            }
        }
    
        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }
}
