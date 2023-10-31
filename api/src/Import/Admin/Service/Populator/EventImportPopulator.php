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

use App\App\Repository\AppRepository;
use App\Event\Entity\Event;
use App\Geography\Entity\Address;
use App\Import\Admin\Interfaces\PopulatorInterface;
use App\Import\Admin\Service\ImportManager;

/**
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class EventImportPopulator extends ImportPopulator implements PopulatorInterface
{
    private const ENTITY = 'App\Event\Entity\Event';

    private const EXTERNAL_ID = 0;
    private const NAME = 1;
    private const FROM_DATE = 2;
    private const FROM_TIME = 3;
    private const TO_DATE = 4;
    private const TO_TIME = 5;
    private const DESCRIPTION = 6;
    private const LATITUDE = 7;
    private const LONGITUDE = 8;
    private const COMMUNITY_ID = 9;

    private const MESSAGE_OK = 'added';
    private const MESSAGE_ALREADY_EXISTS = 'already exists and will be ignored';
    private const MESSAGE_ALREADY_EXISTS_WILL_BE_UPDATED = 'already exists and will be updated';
    private const APP_ID = 1;

    private $appRepository;
    private $_importManager;
    private $_messages;

    public function __construct(ImportManager $importManager, AppRepository $appRepository)
    {
        $this->_importManager = $importManager;
        $this->_messages = [];
        $this->appRepository = $appRepository;
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
        if (!$this->_canAddEvent($line)) {
            $this->_updateEvent($line);

            return;
        }

        $this->_addEvent($line);
    }

    private function _fillEvent(Event $event, array $line): ?Event
    {
        $event->setExternalId($line[self::EXTERNAL_ID]);
        $event->setName($line[self::NAME]);
        $event->setDescription($line[self::DESCRIPTION]);
        if ('' !== $line[self::FROM_TIME] && '' !== $line[self::TO_TIME]) {
            $event->setUseTime(1);
            $event->setFromDate(new \DateTime($line[self::FROM_DATE].' '.$line[self::FROM_TIME]));
            $event->setToDate(new \DateTime($line[self::TO_DATE].' '.$line[self::TO_TIME]));
        } else {
            $event->setUseTime(0);
            $event->setFromDate(new \DateTime($line[self::FROM_DATE]));
            $event->setToDate(new \DateTime($line[self::TO_DATE]));
        }

        $event->setCommunity($this->_importManager->getCommunity($line[self::COMMUNITY_ID]));

        $event->setCreatedDate(new \DateTime('now'));
        $event->setStatus(1);
        $event->setPrivate(0);
        $event->setApp($this->appRepository->find(self::APP_ID));

        $address = new Address();
        $address->setLatitude((float) $line[self::LATITUDE]);
        $address->setLongitude((float) $line[self::LONGITUDE]);

        $event->setAddress($address);

        return $event;
    }

    private function _addEvent(array $line)
    {
        $entity = $this->getEntity();

        /** @var Event $event */
        $event = new $entity();

        if (!$event = $this->_fillEvent($event, $line)) {
            return;
        }

        try {
            $this->_importManager->addEvent($event);
        } catch (\Exception $e) {
            $this->_messages[] = $e->getMessage();

            return;
        }

        $this->_messages[] = $line[self::EXTERNAL_ID].' '.self::MESSAGE_OK;
    }

    private function _updateEvent(array $line)
    {
        if (!$event = $this->_importManager->getEventByExternalId($line[self::EXTERNAL_ID])) {
            return;
        }

        if (!$this->_canUpdateEvent($event, $line[self::EXTERNAL_ID])) {
            $this->_messages[] = $line[self::EXTERNAL_ID].' '.self::MESSAGE_ALREADY_EXISTS.' -> id = '.$event->getId();

            return;
        }

        if (!$relaypoint = $this->_fillEvent($event, $line)) {
            return;
        }

        $this->_messages[] = $line[self::EXTERNAL_ID].' '.self::MESSAGE_ALREADY_EXISTS_WILL_BE_UPDATED.' -> id = '.$event->getId().', externalID = '.$event->getExternalId();

        try {
            $this->_importManager->updateEvent($event);
        } catch (\Exception $e) {
            $this->_messages[] = $e->getMessage();

            return;
        }
    }

    private function _checkEventAlreadyExists(string $externalId): bool
    {
        if (!is_null($this->_importManager->getEventByExternalId($externalId))) {
            return true;
        }

        return false;
    }

    private function _canUpdateEvent(Event $event, string $lineExternalId): bool
    {
        return $event->getExternalId() === $lineExternalId;
    }

    private function _canAddEvent(array $line): bool
    {
        if ($this->_checkEventAlreadyExists($line[self::EXTERNAL_ID])) {
            return false;
        }

        return true;
    }
}
