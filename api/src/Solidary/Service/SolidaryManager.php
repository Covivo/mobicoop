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

use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Event\SolidaryCreated;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUpdated;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use Symfony\Component\Security\Core\Security;

class SolidaryManager
{
    private $entityManager;
    private $eventDispatcher;
    private $security;
    private $solidaryRepository;
    private $solidaryUserRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryRepository $solidaryRepository, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryRepository = $solidaryRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
    }

    public function getSolidary($id): ?Solidary
    {
        $solidary = $this->solidaryRepository->find($id);

        // We find the last entry of diary for this solidary to get the progression
        $diariesEntires = $this->solidaryRepository->getDiaries($solidary);
        (count($diariesEntires)>0) ? $solidary->setProgression($diariesEntires[0]->getProgression()) : $solidary->setProgression(0);

        return $solidary;
    }

    public function createSolidary(Solidary $solidary)
    {
        // We trigger the event
        $event = new SolidaryCreated($solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser(), $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryCreated::NAME, $event);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();
    }

    public function updateSolidary(Solidary $solidary)
    {
        // We trigger the event
        $event = new SolidaryUpdated($solidary);
        $this->eventDispatcher->dispatch(SolidaryUpdated::NAME, $event);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();
    }

    /**
     * Get the results for a Solidary Transport Search
     *
     * @param SolidarySearch $solidarySearch
     * @return SolidarySearch
     */
    public function getSolidaryTransportSearchResults(SolidarySearch $solidarySearch): SolidarySearch
    {
        $solidarySearch->setResults($this->solidaryUserRepository->findForASolidaryTransportSearch($solidarySearch));
        
        return $solidarySearch;
    }

    /**
     * Get the results for a Solidary Carpool Search
     *
     * @param SolidarySearch $solidarySearch
     * @return SolidarySearch
     */
    public function getSolidaryCarpoolSearchSearchResults(SolidarySearch $solidarySearch): SolidarySearch
    {
        $waypoints = $solidarySearch->getSolidary()->getProposal()->getWaypoints();
        $withoutDestination = false;
        foreach ($waypoints as $waypoint) {
            if ($waypoint->isDestination()) {
                $withoutDestination=true;
            }
        }
        
        if ($withoutDestination) {
            $solidarySearch->setResults($this->solidaryUserRepository->findForASolidaryCarpoolSearchWithoutDestination($solidarySearch));
        } else {
            // Call the classic matching algo
        }
        
        return $solidarySearch;
    }
}
