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

namespace App\Communication\Controller;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Repository\AskHistoryRepository;
use App\TranslatorTrait;
use App\Communication\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Controller class for contact message.
 *
 */
class SendAction
{
    use TranslatorTrait;

    private $entityManager;
    private $askHistoryRepository;
    
    public function __construct(EntityManagerInterface $entityManager, AskHistoryRepository $askHistoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->askHistoryRepository = $askHistoryRepository;
    }

    public function __invoke(Message $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad message  id is provided"));
        }
        
        // This message is related to an Ask
        if ($data->getIdAsk()!==null) {
            
            // We get the infos of the Ask
            $ask = $this->entityManager->getRepository(Ask::class)->find($data->getIdAsk());
            
            // Create the new AskHistory
            $askHistory = new AskHistory();

            $askHistory->setMessage($data);
            $askHistory->setAsk($ask);
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());

            $this->entityManager->persist($askHistory);

            // Update the updated date of the Ask
            $ask->setUpdatedDate(new \DateTime("now"));
            $this->entityManager->persist($ask);
        }

        return $data;
    }
}
