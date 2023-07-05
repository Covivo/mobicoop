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

namespace App\Import\Admin\Service\Populator;

use App\Import\Admin\Interfaces\PopulatorInterface;
use App\User\Admin\Service\UserManager;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserImportPopulator implements PopulatorInterface
{
    private const ENTITY = 'App\User\Entity\User';

    private const EMAIL = 0;
    private const GIVEN_NAME = 1;
    private const FAMILY_NAME = 2;
    private const GENDER = 3;
    private const BIRTHDATE = 4;
    private const PHONE_NUMBER = 5;

    private $_userManager;

    public function __construct(UserManager $userManager)
    {
        $this->_userManager = $userManager;
    }

    public function populate(File $file)
    {
        $openedFile = fopen($file, 'r');

        while (!feof($openedFile)) {
            $line = fgetcsv($openedFile, 0, ';');
            if ($line) {
                $this->_addUser($line);
            }
        }

        fclose($openedFile);
    }

    public function getEntity(): string
    {
        return self::ENTITY;
    }

    private function _addUser(array $line)
    {
        $entity = $this->getEntity();

        /** @var User $user */
        $user = new $entity();
        $user->setEmail($line[self::EMAIL]);
        $user->setStatus(User::STATUS_ACTIVE);
        $user->setGender($line[self::GENDER]);
        $user->setBirthDate(new \DateTime($line[self::BIRTHDATE]));
        $user->setGivenName($line[self::GIVEN_NAME]);
        $user->setFamilyName($line[self::FAMILY_NAME]);
        $user->setTelephone($line[self::PHONE_NUMBER]);

        $this->_userManager->addUser($user);
    }
}
