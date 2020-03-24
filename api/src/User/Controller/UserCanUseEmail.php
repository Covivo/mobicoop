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
 **************************/

namespace App\User\Controller;

use App\TranslatorTrait;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use App\User\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserCanUseEmail
{
    use TranslatorTrait;
    private $userManager;
    private $request;
    
    public function __construct(UserManager $userManager, RequestStack $request)
    {
        $this->userManager = $userManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * This method is invoked whan we check if the email is already used.
     *
     * @return void
     */
    public function __invoke()
    {
        if ($this->userManager->getUserByEmail($this->request->get('email'))) {
            throw new \InvalidArgumentException($this->translator->trans("Email already in use"));
        }
        return null;
    }
}
