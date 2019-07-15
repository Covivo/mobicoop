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
 **************************/

namespace App\Carpool\Service;

use App\Carpool\Entity\Ask;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Carpool\Event\AskPostedEvent;

/**
 * Ask manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AskManager
{
    private $eventDispatcher;
    private $entityManager;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    
    /**
     * Create an ask.
     *
     */
    public function createAsk(Ask $ask)
    {
        // todo : check if an ask already exists for the match and the proposals
        
        $this->entityManager->persist($ask);
        // dispatch en event
        $event = new AskPostedEvent($ask);
        $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        return $ask;
    }
}
