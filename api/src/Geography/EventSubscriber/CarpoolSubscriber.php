<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Geography\EventSubscriber;

use App\Carpool\Event\MatchingNewEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\ClassNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarpoolSubscriber implements EventSubscriberInterface
{
    public const FILENAME = 'matching_';

    private $logger;
    private $directory;

    public function __construct(LoggerInterface $logger, string $directory)
    {
        $this->logger = $logger;
        $this->directory = $directory;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MatchingNewEvent::NAME => 'onNewMatching',
        ];
    }

    /**
     * Executed when a new matching is discovered.
     *
     * @throws ClassNotFoundException
     */
    public function onNewMatching(MatchingNewEvent $event): void
    {
        // When a new matching is created, we need to be sure that directions are completely filled with the good data.
        // To speed up the process during the creation of the proposal, we avoid sending all the data to the database.
        // We need now to compute and send this data, this is done by an external script, we just have to tell the script to execute.
        // This is done by writing a file in a special directory.
        // $filename = $this->directory . self::FILENAME . (new \DateTime("UTC"))->format("YmdHisu") . ".txt";
        // $fp = fopen($filename, 'w');
        // fwrite($fp, $event->getMatching()->getId());
        // fclose($fp);
    }
}
