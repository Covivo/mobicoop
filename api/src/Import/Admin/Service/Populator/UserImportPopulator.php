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

use App\Geography\Entity\Address;
use App\Geography\Service\PointSearcher;
use App\Import\Admin\Interfaces\PopulatorInterface;
use App\Import\Admin\Service\ImportManager;
use App\User\Entity\User;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class UserImportPopulator extends ImportPopulator implements PopulatorInterface
{
    private const ENTITY = 'App\User\Entity\User';

    private const EMAIL = 0;
    private const GIVEN_NAME = 1;
    private const FAMILY_NAME = 2;
    private const GENDER = 3;
    private const BIRTHDATE = 4;
    private const PHONE_NUMBER = 5;
    private const COMMUNITY_ID = 6;
    private const HOUSE_NUMBER = 7;
    private const STREET_ADDRESS = 8;
    private const POSTAL_CODE = 9;
    private const ADDRESS_LOCALITY = 10;
    private const ADDRESS_COUNTRY = 11;
    private const ADDRESS_LATITUDE = 12;
    private const ADDRESS_LONGITUDE = 13;
    private const CONSENT = 14;

    private const MESSAGE_OK = 'added';
    private const MESSAGE_ALREADY_EXISTS = 'already exists';
    private const UNKNOWN_COMMUNITY = 'community is unknown';

    private $_importManager;
    private $_messages;

    /**
     * @var User
     */
    private $_user;

    /**
     * @var User
     */
    private $_requester;

    /**
     * @var PointSearcher
     */
    private $_pointSearcher;

    public function __construct(ImportManager $importManager, User $requester, ?PointSearcher $pointSearcher)
    {
        $this->_importManager = $importManager;
        $this->_messages = [];
        $this->_requester = $requester;
        $this->_user = null;
        $this->_pointSearcher = $pointSearcher;
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
        if (!$this->_canAddUser($line) || !$this->_checkCommunity($line)) {
            return;
        }

        $entity = $this->getEntity();

        // @var User $user
        $this->_user = new $entity();
        $this->_user->setEmail($line[self::EMAIL]);
        $this->_user->setStatus(User::STATUS_ACTIVE);
        $this->_user->setGender($line[self::GENDER]);
        $this->_user->setBirthDate(new \DateTime($line[self::BIRTHDATE]));
        $this->_user->setGivenName($line[self::GIVEN_NAME]);
        $this->_user->setFamilyName($line[self::FAMILY_NAME]);
        $this->_user->setTelephone($line[self::PHONE_NUMBER]);
        $this->_user->setUserDelegate($this->_requester);
        $this->_user->setImportedDate(new \DateTime('now'));
        $this->_user->setNewsSubscription($line[self::CONSENT]);
        $this->_user->setHomeAddress($this->_treatLocality($line));

        try {
            $this->_user = $this->_importManager->addUser($this->_user);
            $this->_treatCommunity($line);
        } catch (\Exception $e) {
            $this->addMessage($e->getMessage());

            return;
        }

        $this->addMessage($line[self::EMAIL].' '.self::MESSAGE_OK);
    }

    private function _checkCommunity(array $line): bool
    {
        if ('' === trim($line[self::COMMUNITY_ID])) {
            return true;
        }
        if ($this->_importManager->getCommunity($line[self::COMMUNITY_ID])) {
            return true;
        }

        $this->addMessage($line[self::COMMUNITY_ID].' '.self::UNKNOWN_COMMUNITY);

        return false;
    }

    private function _treatCommunity(array $line)
    {
        if (!is_numeric($line[self::COMMUNITY_ID])) {
            return;
        }
        if ($community = $this->_importManager->getCommunity($line[self::COMMUNITY_ID])) {
            $this->_importManager->signUpUserInACommunity($community, $this->_user);
        }
    }

    private function _buildCompleteAddress(array $line): Address
    {
        $address = new Address();
        $address->setLatitude(('' !== trim($line[self::ADDRESS_LATITUDE])) ? $line[self::ADDRESS_LATITUDE] : null);
        $address->setLongitude(('' !== trim($line[self::ADDRESS_LONGITUDE])) ? $line[self::ADDRESS_LONGITUDE] : null);
        $address->setHouseNumber(('' !== trim($line[self::HOUSE_NUMBER])) ? $line[self::HOUSE_NUMBER] : null);
        $address->setStreetAddress(('' !== trim($line[self::STREET_ADDRESS])) ? $line[self::STREET_ADDRESS] : null);
        $address->setPostalCode(('' !== trim($line[self::POSTAL_CODE])) ? $line[self::POSTAL_CODE] : null);
        $address->setAddressLocality(('' !== trim($line[self::ADDRESS_LOCALITY])) ? $line[self::ADDRESS_LOCALITY] : null);
        $address->setAddressCountry(('' !== trim($line[self::ADDRESS_COUNTRY])) ? $line[self::ADDRESS_COUNTRY] : null);

        return $address;
    }

    private function _treatLocality(array $line): ?Address
    {
        if (!is_null($line[self::ADDRESS_LATITUDE])
        && !is_null($line[self::ADDRESS_LONGITUDE])
        && '' !== trim($line[self::ADDRESS_LATITUDE])
        && '' !== trim($line[self::ADDRESS_LONGITUDE])) {
            return $this->_buildCompleteAddress($line);
        }

        $searchedLocalityArray = [];
        if ('' !== trim($line[self::HOUSE_NUMBER])) {
            $searchedLocalityArray[] = trim($line[self::HOUSE_NUMBER]);
        }
        if ('' !== trim($line[self::STREET_ADDRESS])) {
            $searchedLocalityArray[] = trim($line[self::STREET_ADDRESS]);
        }
        if ('' !== trim($line[self::POSTAL_CODE])) {
            $searchedLocalityArray[] = trim($line[self::POSTAL_CODE]);
        }
        if ('' !== trim($line[self::ADDRESS_LOCALITY])) {
            $searchedLocalityArray[] = trim($line[self::ADDRESS_LOCALITY]);
        }
        if ('' !== trim($line[self::ADDRESS_COUNTRY])) {
            $searchedLocalityArray[] = trim($line[self::ADDRESS_COUNTRY]);
        }

        if (0 == count($searchedLocalityArray)) {
            return null;
        }

        $results = $this->_pointSearcher->geocode(implode(' ', $searchedLocalityArray));

        if (!empty($results)) {
            $homeAddress = new Address();
            $homeAddress->createFromPoint($results[0]);

            return $homeAddress;
        }

        return null;
    }

    private function _checkUserAlreadyExists(string $email): bool
    {
        if (!is_null($this->_importManager->getUserByEmail($email))) {
            return true;
        }

        return false;
    }

    private function _canAddUser(array $line): bool
    {
        if ($this->_checkUserAlreadyExists($line[self::EMAIL])) {
            $this->addMessage($line[self::EMAIL].' '.self::MESSAGE_ALREADY_EXISTS);

            return false;
        }

        return true;
    }
}
