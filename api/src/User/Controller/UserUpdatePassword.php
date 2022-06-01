<?php

 /**
  * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
  */

namespace App\User\Controller;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use App\TranslatorTrait;
use App\User\Entity\User;
use App\User\Service\UserManager;

class UserUpdatePassword
{
    use TranslatorTrait;

    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * This method is invoked when a user try to update or ask the update of his password.
     * It returns the altered user.
     *
     * @param string $data
     *
     * @throws InvalidArgumentException
     */
    public function __invoke(User $data, string $name): Response
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad User id is provided'));
        }

        switch ($name) {
            case 'request':
                $data = $this->userManager->updateUserPasswordRequest($data);

                break;

            case 'update':
                $data = $this->userManager->updateUserPassword($data);

                break;

            default:
                throw new InvalidArgumentException($this->translator->trans('Unrecognized parameter :%name%', ['name' => $name]));
        }

        return $data;
    }
}
