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

use App\Carpool\Service\AdManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Event\SolidaryCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
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
    private $adManager;
    private $solidaryMatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryRepository $solidaryRepository, SolidaryUserRepository $solidaryUserRepository, AdManager $adManager, SolidaryMatcher $solidaryMatcher)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryRepository = $solidaryRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->adManager = $adManager;
        $this->solidaryMatcher = $solidaryMatcher;
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
        $event = new SolidaryCreatedEvent($solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser(), $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();
    }

    public function updateSolidary(Solidary $solidary)
    {
        // We trigger the event
        $event = new SolidaryUpdatedEvent($solidary);
        $this->eventDispatcher->dispatch(SolidaryUpdatedEvent::NAME, $event);

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

        // We make an Ad from the proposal linked to the solidary (if it's on the return, we take the ProposalLinked)
        // I'll have the results directly in the Ad
        
        if ($solidarySearch->getWay()=="outward") {
            $proposal = $solidarySearch->getSolidary()->getProposal();
        } else {
            if (!is_null($solidarySearch->getSolidary()->getProposal()->getProposalLinked())) {
                $proposal = $solidarySearch->getSolidary()->getProposal()->getProposalLinked();
            }
            throw new SolidaryException(SolidaryException::NO_RETURN_PROPOSAL);
        }

        // If the proposal doesn't have any destination, we duplicate the origin waypoint
        $waypoints = $proposal->getWaypoints();
        $withDestination = false;
        foreach ($waypoints as $waypoint) {
            if ($waypoint->isDestination()) {
                $withDestination=true;
            }
        }
        if (!$withDestination) {
            // We clone the first waypoint and we use it as destination
            $newDestination = clone $waypoints[0];
            $newDestination->setDestination(true);
            $proposal->addWaypoint(clone $newDestination);
        }

        $ad = $this->adManager->makeAd($proposal, $proposal->getUser()->getId());
        // echo count($ad->getResults());die;
        // We need to build and persist all the new results as SolidaryMatching.
        $solidaryMatchings = $this->solidaryMatcher->buildSolidaryMatchingsForCarpool($solidarySearch->getSolidary(), $ad->getResults());

        // We make Solidary Results out of the Ad's results
        $results = [];
        foreach ($solidaryMatchings as $solidaryMatching) {
            $results[] = $this->solidaryMatcher->buildSolidaryResultCarpool($solidaryMatching);
        }
        $solidarySearch->setResults($results);

        return $solidarySearch;
    }
}
