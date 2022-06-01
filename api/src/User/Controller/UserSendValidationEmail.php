<?php

 /**
    * Copyright (c) 2020, MOBICOOP. All rights reserved.
    * This project is dual licensed under AGPL and proprietary licence.
    ***************************
    * This program is free software: you can redistribute it and/or modify
    * it under the terms of the GNU Affero General Public License as
    * published by the Free Software Foundation, either version 3 of the
    * License, or (at your option) any later version.
    *
    * This program is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU Affero General Public License for more details.
    *
    * You should have received a copy of the GNU Affero General Public License
    * along with this program. If not, see <gnu.org/licenses>.
    ***************************
    * Licence MOBICOOP described in the file
    * LICENSE
    **************************/

 namespace App\User\Controller;

use App\TranslatorTrait;
use App\User\Entity\User;
use App\User\Service\UserManager;

/**
 * Controller class to send a confirmation email
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class UserSendValidationEmail
{
    use TranslatorTrait;
    /**
     * @var UserManager $userManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager= $userManager;
    }

    /**
     * Send a validation email to the user
     *
     * @param User $user the user
     * @return User
     */
    public function __invoke(User $user): User
    {
        if (is_null($user)) {
            throw new \InvalidArgumentException($this->translator->trans("bad User id is provided"));
        }

        return $this->userManager->sendValidationEmail($user->getEmail());
    }
}
