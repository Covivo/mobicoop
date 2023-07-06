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

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RelayPointImportPopulator extends ImportPopulator implements PopulatorInterface
{
    private const ENTITY = 'App\User\Entity\User';

    private const EMAIL = 0;
    private const GIVEN_NAME = 1;
    private const FAMILY_NAME = 2;
    private const GENDER = 3;
    private const BIRTHDATE = 4;
    private const PHONE_NUMBER = 5;

    private const MESSAGE_OK = 'added';
    private const MESSAGE_ALREADY_EXISTS = 'already exists';

    private $_userManager;
    private $_messages;

    public function __construct(UserManager $userManager)
    {
        $this->_userManager = $userManager;
        $this->_messages = [];
    }

    public function getEntity(): string
    {
        return self::ENTITY;
    }

    public function getMessages(): array
    {
        return $this->_messages;
    }

    public function addMessage(string $message): array
    {
        $this->_messages[] = $message;

        return $this->_messages;
    }

    protected function _addEntity(array $line)
    {
        if (!$this->_canAddUser($line)) {
            return;
        }

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

        try {
            $this->_userManager->addUser($user);
        } catch (\Exception $e) {
            $this->_messages[] = $e->getMessage();

            return;
        }

        $this->_messages[] = $line[self::EMAIL].' '.self::MESSAGE_OK;
    }

    private function _checkUserAlreadyExists(string $email): bool
    {
        if (!is_null($this->_userManager->getUserByEmail($email))) {
            return true;
        }

        return false;
    }

    private function _canAddUser(array $line): bool
    {
        if ($this->_checkUserAlreadyExists($line[self::EMAIL])) {
            $this->_messages[] = $line[self::EMAIL].' '.self::MESSAGE_ALREADY_EXISTS;

            return false;
        }

        return true;
    }
}
