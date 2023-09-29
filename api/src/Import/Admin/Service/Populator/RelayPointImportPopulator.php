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
use App\Import\Admin\Interfaces\PopulatorInterface;
use App\Import\Admin\Service\ImportManager;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Entity\RelayPointType;
use App\User\Entity\User;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RelayPointImportPopulator extends ImportPopulator implements PopulatorInterface
{
    private const ENTITY = 'App\RelayPoint\Entity\RelayPoint';

    private const NAME = 0;
    private const TYPE = 1;
    private const LATITUDE = 2;
    private const LONGITUDE = 3;
    private const PLACES = 4;
    private const SECURED = 5;
    private const FREE = 6;
    private const OFFICIAL = 7;
    private const PRIVATE = 8;
    private const EXTERNAL_ID = 9;

    private const MESSAGE_OK = 'added';
    private const MESSAGE_ALREADY_EXISTS = 'already exists';
    private const RELAYPOINT_TYPE_UNKNOWN = 'RelayPointType unknown for';

    private $_importManager;
    private $_messages;

    /**
     * @var User
     */
    private $_requester;

    public function __construct(ImportManager $importManager, User $requester)
    {
        $this->_importManager = $importManager;
        $this->_messages = [];
        $this->_requester = $requester;
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
        if (!$this->_canAddRelayPoint($line)) {
            return;
        }

        $relayPointType = $this->_getRelayPointType($line[self::TYPE]);
        if (is_null($relayPointType)) {
            $this->addMessage(self::RELAYPOINT_TYPE_UNKNOWN.' '.$this->_getLabel($line).' Type : '.$line[self::TYPE]);

            return;
        }

        $entity = $this->getEntity();

        /** @var RelayPoint $relaypoint */
        $relaypoint = new $entity();
        $relaypoint->setCreatorId($this->_requester->getId());
        $relaypoint->setName($line[self::NAME]);
        $relaypoint->setRelayPointType($relayPointType);
        $relaypoint->setPlaces((int) $line[self::PLACES]);
        $relaypoint->setSecured((bool) $line[self::SECURED]);
        $relaypoint->setFree((bool) $line[self::FREE]);
        $relaypoint->setOfficial((bool) $line[self::OFFICIAL]);
        $relaypoint->setPrivate((bool) $line[self::PRIVATE]);
        $relaypoint->setStatus(RelayPoint::STATUS_ACTIVE);

        if ('' !== trim($line[self::EXTERNAL_ID])) {
            $relaypoint->setExternalId($line[self::EXTERNAL_ID]);
        }

        $relaypoint->setImportedDate(new \DateTime('now'));

        $address = new Address();
        $address->setLatitude((float) $line[self::LATITUDE]);
        $address->setLongitude((float) $line[self::LONGITUDE]);

        $relaypoint->setAddress($address);

        try {
            $this->_importManager->addRelayPoint($relaypoint);
        } catch (\Exception $e) {
            $this->_messages[] = $e->getMessage();

            return;
        }

        $this->_messages[] = $this->_getLabel($line).' '.self::MESSAGE_OK;
    }

    private function _getRelayPointType(int $id): ?RelayPointType
    {
        return $this->_importManager->getRelayPointTypeById($id);
    }

    private function _getLabel(array $line)
    {
        return $line[self::NAME].' ('.$line[self::LATITUDE].';'.$line[self::LONGITUDE].')';
    }

    private function _checkRelayPointAlreadyExists(float $latitude, float $longitude, string $externalId): ?RelayPoint
    {
        if ('' == trim($externalId)) {
            $results = $this->_importManager->getByLatLon($latitude, $longitude);
        } else {
            $results = $this->_importManager->getByLatLonOrExternalId($latitude, $longitude, $externalId);
        }

        if (is_array($results) && count($results) > 0) {
            return $results[0];
        }

        return null;
    }

    private function _canAddRelayPoint(array $line): bool
    {
        if ($relaypoint = $this->_checkRelayPointAlreadyExists((float) $line[self::LATITUDE], (float) $line[self::LONGITUDE], $line[self::EXTERNAL_ID])) {
            $this->_messages[] = $this->_getLabel($line).' '.self::MESSAGE_ALREADY_EXISTS.' -> id = '.$relaypoint->getId();

            return false;
        }

        return true;
    }
}
