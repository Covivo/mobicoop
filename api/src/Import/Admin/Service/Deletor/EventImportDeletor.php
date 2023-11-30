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

namespace App\Import\Admin\Service\Deletor;

use App\Event\Entity\Event;
use App\Import\Admin\Interfaces\DeletorInterface;
use App\Import\Admin\Service\ImportManager;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class EventImportDeletor extends ImportDeletor implements DeletorInterface
{
    private const ENTITY = 'App\Event\Entity\Event';

    private const EXTERNAL_ID = 0;

    private const MESSAGE_OK = 'deleted';

    private $_importManager;
    private $_messages;

    public function __construct(ImportManager $importManager)
    {
        $this->_importManager = $importManager;
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

    protected function _deleteEntity(File $file)
    {
        $openedFile = fopen($file, 'r');

        $externalIds = [];
        while (!feof($openedFile)) {
            $line = fgetcsv($openedFile, 0, ';');
            if ($line) {
                $externalIds[] = $line[self::EXTERNAL_ID];
            }
        }

        fclose($openedFile);

        $this->_deleteEvents($externalIds);
    }

    private function _deleteEvents(array $externalIds)
    {
        $entity = $this->getEntity();

        /** @var Event $event */
        $event = new $entity();

        $events = $this->_getAllEvents()->getQuery()->getResult();

        foreach ($events as $event) {
            if (!in_array($event->getExternalId(), $externalIds)) {
                try {
                    $this->_importManager->deleteEvent($event);
                } catch (\Exception $e) {
                    $this->_messages[] = $e->getMessage();

                    return;
                }

                $this->_messages[] = $event->getExternalId().' '.self::MESSAGE_OK;
            }
        }
    }

    private function _getAllEvents()
    {
        return $this->_importManager->getAllEvents();
    }
}
