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

namespace App\User\Controller;

use App\TranslatorTrait;
use App\User\Service\UserManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for user delegate registration (password is sent in clear, encoding is made on api side).
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class UserDelegateRegistration
{
    use TranslatorTrait;
    private $userManager;
    private $request;

    public function __construct(RequestStack $requestStack, UserManager $userManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->userManager = $userManager;
    }

    /**
     * This method is invoked when a new user registers.
     * It returns the new user created.
     *
     * @param User $data
     * @return User
     */
    public function __invoke(User $data): User
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad User id is provided"));
        }
        $data = $this->userManager->registerUser($data, false);
        return $data;
    }
}
