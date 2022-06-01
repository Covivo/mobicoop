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
 */

namespace App\Solidary\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Matching;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
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
     * Create a solidary Ask.
     */
    public function createSolidaryAsk(SolidaryAsk $solidaryAsk): ?SolidaryAsk
    {
        // We create the associated SolidaryAskHistory
        $solidaryAsk = $this->createAssociatedSolidaryAskHistory($solidaryAsk);

        // If it's a Carpool Ask type we need to create the related Ask
        if (!is_null($solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
            // The User who make the Ask
            $user = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();

            // We get the matching to have all criterias
            $matching = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching();

            // create the carpool Ask
            $ask = $this->createAskFromMatching($matching, $user);

            // We set the link between the Ask and the SolidaryAsk
            $ask->setSolidaryAsk($solidaryAsk);

            // We create the associated Ask History
            $askHistory = new AskHistory();
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $ask->addAskHistory($askHistory);

            $this->entityManager->persist($ask);
            $this->entityManager->flush();

            // We link the solidary Ask to this new Ask
            $solidaryAsk->setAsk($ask);

            $this->entityManager->persist($solidaryAsk);
            $this->entityManager->flush();

            // If there is a matchinglinked, we need to create an Ask for the return trip
            if (!is_null($matching->getMatchingLinked())) {
                $askLinked = $this->createAskFromMatching($matching->getMatchingLinked(), $user);

                // We create the associated Ask History
                $askLinkedHistory = new AskHistory();
                $askLinkedHistory->setStatus($askLinked->getStatus());
                $askLinkedHistory->setType($askLinked->getType());
                $askLinked->addAskHistory($askLinkedHistory);

                $this->entityManager->persist($askLinked);
                $this->entityManager->flush();

                // We link this "return" Ask to the "outward" Ask
                $ask->setAskLinked($askLinked);
                $this->entityManager->persist($ask);
                $this->entityManager->flush();
            }
        }

        return $solidaryAsk;
    }

    /**
     * Create the associated SolidaryAskHistory of a SolidaryAsk.
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

    /**
     * Create an Ask from a matching informations.
     *
     * @param Matching $matching Matching using as base for the Ask Matching
     * @param User     $user     The User who make the Ask
     */
    private function createAskFromMatching(Matching $matching, User $user): Ask
    {
        $ask = new Ask();
        $ask->setStatus(Ask::STATUS_INITIATED);

        $ask->setType(1); // One way. Default value.
        $ask->setUser($user);

        // The User related to the Ask
        $userRelated = $matching->getProposalOffer()->getUser();

        $ask->setMatching($matching);
        $ask->setUserRelated($userRelated);
        $ask->setUserDelegate($this->security->getUser()); // The admin or the user that make the Ask for the true User
        $criteria = clone $matching->getCriteria();
        $ask->setCriteria($criteria);

        // we use the matching waypoints
        $waypoints = $matching->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $newWaypoint = clone $waypoint;
            $ask->addWaypoint($newWaypoint);
        }

        return $ask;
    }
}
