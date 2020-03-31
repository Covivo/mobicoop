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
use App\Carpool\Entity\AskHistory;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\Solidary\Exception\SolidaryException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class SolidaryAskManager
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Create a solidary Ask
     *
     * @param SolidaryAsk $solidaryAsk
     * @return SolidaryAsk|null
     */
    public function createSolidaryAsk(SolidaryAsk $solidaryAsk): ?SolidaryAsk
    {
        
        // We create the associated SolidaryAskHistory
        $solidaryAsk = $this->createAssociatedSolidaryAskHistory($solidaryAsk);

        // If it's a Carpool Ask type we need to create the related Ask
        if (!is_null($solidaryAsk->getSolidarySolution()->getMatching())) {
            // create the carpool Ask
            $ask = new Ask();
            $ask->setStatus(Ask::STATUS_PENDING_AS_PASSENGER);

            $solidaryProposal = $solidaryAsk->getSolidarySolution()->getSolidary()->getProposal();

            // We get the Poposal
            $ask->setType($solidaryProposal->getType());
            $ask->setUser($solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser());

            // We get the matching to have all criterias
            $matching = $solidaryAsk->getSolidarySolution()->getMatching();
            $ask->setMatching($matching);
            $ask->setUserRelated($matching->getProposalOffer()->getUser());
            $criteria = clone $matching->getCriteria();
            $ask->setCriteria($criteria);

            // we use the matching waypoints
            $waypoints = $matching->getWaypoints();
            foreach ($waypoints as $waypoint) {
                $newWaypoint = clone $waypoint;
                $ask->addWaypoint($newWaypoint);
            }

            // We create the associated Ask History
            $askHistory = new AskHistory();
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $ask->addAskHistory($askHistory);
            
            // // message
            // if (!is_null($dynamicAsk->getMessage())) {
            //     $message = new Message();
            //     $message->setUser($dynamicAsk->getUser());
            //     $message->setText($dynamicAsk->getMessage());
            //     $recipient = new Recipient();
            //     $recipient->setUser($ask->getUserRelated());
            //     $recipient->setStatus(Recipient::STATUS_PENDING);
            //     $message->addRecipient($recipient);
            //     $this->entityManager->persist($message);
            //     $askHistory->setMessage($message);
            // }
            
            $this->entityManager->persist($ask);
            $this->entityManager->flush();
        }

        return $solidaryAsk;
    }

    /**
     * Update a solidary Ask
     *
     * @param SolidaryAsk $solidaryAsk
     * @return SolidaryAsk|null
     */
    public function updateSolidaryAsk(SolidaryAsk $solidaryAsk): ?SolidaryAsk
    {
        
        // We create the associated SolidaryAskHistory
        $solidaryAsk = $this->createAssociatedSolidaryAskHistory($solidaryAsk);

        // If it's a Carpool Ask type we need to update the related Ask
        if (!is_null($solidaryAsk->getSolidarySolution()->getMatching())) {
            // update the carpool Ask
        }

        return $solidaryAsk;
    }

    /**
     * Create the associated SolidaryAskHistory of a SolidaryAsk
     *
     * @param SolidaryAsk $solidaryAsk
     * @return SolidaryAsk|null
     */
    private function createAssociatedSolidaryAskHistory(SolidaryAsk $solidaryAsk): ?SolidaryAsk
    {
        $solidaryAskHistory = new SolidaryAskHistory();
        
        $solidaryAskHistory->setStatus($solidaryAsk->getStatus());
        $solidaryAskHistory->setSolidaryAsk($solidaryAsk);

        $this->entityManager->persist($solidaryAskHistory);
        $this->entityManager->flush();

        return $solidaryAsk;
    }
}
