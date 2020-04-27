<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Criteria;
use App\Communication\Entity\Medium;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryContact;
use App\Solidary\Event\SolidaryContactEmail;
use App\Solidary\Event\SolidaryContactMessage;
use App\Solidary\Event\SolidaryContactSms;
use App\Solidary\Repository\SolidaryAskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

class SolidaryContactManager
{
    private $entityManager;
    private $eventDispatcher;
    private $security;
    private $solidaryAskRepository;
    private $solidaryAskManager;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryAskRepository $solidaryAskRepository, SolidaryAskManager $solidaryAskManager)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryAskRepository = $solidaryAskRepository;
        $this->solidaryAskManager = $solidaryAskManager;
    }

    /**
     * Handle a SolidaryContact
     *
     * @param SolidaryContact $solidaryContact
     * @return SolidaryContact
     */
    public function handleSolidaryContact(SolidaryContact $solidaryContact)
    {
        // We check if there is already an Ask for the solidarySolution in the SolidaryContact
        $solidaryAsk = $this->solidaryAskRepository->findBySolidarySolution($solidaryContact->getSolidarySolution());
        
        if (empty($solidaryAsk)) {
            // There is no SolidaryAsk we need to create it before trigger the event
            $solidaryAsk = new SolidaryAsk();
            $solidaryAsk->setStatus(0);
            $solidaryAsk->setSolidarySolution($solidaryContact->getSolidarySolution());
            $criteria = clone $solidaryContact->getSolidarySolution()->getSolidaryMatching()->getCriteria();
            $solidaryAsk->setCriteria($criteria);
            $solidaryAsk = $this->solidaryAskManager->createSolidaryAsk($solidaryAsk);
            
            // We set the solidaryAsk field for the return
            $solidaryContact->setSolidaryAsk($solidaryAsk);
        }
        
        // we trigger the solidaryContact events
        $media = $solidaryContact->getMedia();
        foreach ($media as $medium) {
            switch ($medium->getId()) {
                case Medium::MEDIUM_MESSAGE:
                    $event = new SolidaryContactMessage($solidaryContact);
                    $this->eventDispatcher->dispatch(SolidaryContactMessage::NAME, $event);
                    break;
                case Medium::MEDIUM_SMS:
                    $event = new SolidaryContactSms($solidaryContact);
                    $this->eventDispatcher->dispatch(SolidaryContactSms::NAME, $event);
                    break;
                case Medium::MEDIUM_EMAIL:
                    $event = new SolidaryContactEmail($solidaryContact);
                    $this->eventDispatcher->dispatch(SolidaryContactEmail::NAME, $event);
                    break;
            }
        }

        return $solidaryContact;
    }
}
