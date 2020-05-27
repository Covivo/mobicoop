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

use App\Carpool\Entity\Criteria;
use App\Carpool\Service\AdManager;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryAsksListItem;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Event\SolidaryCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryAskRepository;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use Symfony\Component\Security\Core\Security;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryVolunteerPlanning\SolidaryVolunteerPlanning;
use App\Solidary\Entity\SolidaryVolunteerPlanning\SolidaryVolunteerPlanningItem;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryManager
{
    private $entityManager;
    private $eventDispatcher;
    private $security;
    private $solidaryRepository;
    private $solidaryAskRepository;
    private $solidaryUserRepository;
    private $adManager;
    private $solidaryMatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryRepository $solidaryRepository, SolidaryUserRepository $solidaryUserRepository, AdManager $adManager, SolidaryMatcher $solidaryMatcher, SolidaryAskRepository $solidaryAskRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryRepository = $solidaryRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->adManager = $adManager;
        $this->solidaryMatcher = $solidaryMatcher;
        $this->solidaryAskRepository = $solidaryAskRepository;
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

    /**
     * Get the solidary solutions of a solidary
     *
     * @param int $solidaryId Id of the Solidary
     * @return array|null
     */
    public function getSolidarySolutions(int $solidaryId): array
    {
        return $this->solidaryRepository->findSolidarySolutions($solidaryId);
    }

    
    /**
     * Get the list of all the Asks (Solidary or not) linked to a Solidary
     *
     * @param integer $solidaryId
     * @return Solidary
     */
    public function getAsksList(int $solidaryId): Solidary
    {
        $asksList = [];

        $solidaryAsks = $this->solidaryAskRepository->findSolidaryAsks($solidaryId);

        foreach ($solidaryAsks as $solidaryAsk) {

            /**
             * @var SolidaryAsk $solidaryAsk
             */

            $solidaryAsksItem = new SolidaryAsksListItem();

            $askCriteria = $askCriteriaReturn = null;
            if (!is_null($solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
                // Carpool
                $user = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
                $askCriteria = $solidaryAsk->getCriteria();
                if (!is_null($solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getMatchingLinked())) {
                    $askCriteriaReturn = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getMatchingLinked()->getCriteria();
                }

                $solidaryAsksItem->setDriverType(SolidaryAsksListItem::DRIVER_TYPE_CARPOOLER);
            } else {
                // Solidary Transport
                $user = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
                $askCriteria = $solidaryAsk->getCriteria();

                $solidaryAsksItem->setDriverType(SolidaryAsksListItem::DRIVER_TYPE_VOLUNTEER);
            }


            // Frequency
            $solidaryAsksItem->setFrequency($askCriteria->getFrequency());
            
            // Status
            $solidaryAsksItem->setStatus($solidaryAsk->getStatus());

            // FromDate, to date
            $solidaryAsksItem->setFromDate($askCriteria->getFromDate());
            $days = ["mon","tue","wed","thu","fri","sat","sun"];

            $schedule = [];
            if ($askCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                // Regular journey
                $solidaryAsksItem->setToDate($askCriteria->getToDate());
                
                $schedule = $this->adManager->getScheduleFromCriteria($askCriteria, $askCriteriaReturn);
            } else {
                // Punctual journey
                $schedule[0]['outwardTime'] = $askCriteria->getFromTime()->format("H:i");
                if (!is_null($askCriteriaReturn)) {
                    $schedule[0]['returnTime'] = $askCriteriaReturn->getFromTime()->format("H:i");
                }
                // init days
                foreach ($days as $day) {
                    $schedule[0][$day] = false;
                }
                if ($askCriteria->isMonCheck()) {
                    $schedule[0]["mon"]=true;
                }
                if ($askCriteria->isTueCheck()) {
                    $schedule[0]["tue"]=true;
                }
                if ($askCriteria->isWedCheck()) {
                    $schedule[0]["wed"]=true;
                }
                if ($askCriteria->isThuCheck()) {
                    $schedule[0]["thu"]=true;
                }
                if ($askCriteria->isFriCheck()) {
                    $schedule[0]["fri"]=true;
                }
                if ($askCriteria->isSatCheck()) {
                    $schedule[0]["sat"]=true;
                }
                if ($askCriteria->isSunCheck()) {
                    $schedule[0]["sun"]=true;
                }
            }

            $solidaryAsksItem->setSchedule($schedule);

            // The driver
            $solidaryAsksItem->setDriver($user->getGivenName()." ".$user->getFamilyName());
            $solidaryAsksItem->setTelephone($user->getTelephone());

            // SolidarySolutionId (usefull for a SolidaryContact)
            $solidaryAsksItem->setSolidarySolutionId($solidaryAsk->getSolidarySolution()->getId());

            $asksList[] = $solidaryAsksItem;
        }

        $solidary = $this->getSolidary($solidaryId);
        $solidary->setAsksList($asksList);

        return $solidary;
    }

    /**
     * Build a volunteer's planning between two dates.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param integer $solidaryVolunteerId
     * @return array
     */
    public function buildSolidaryVolunteerPlanning(\DateTimeInterface $startDate, \DateTimeInterface  $endDate, int $solidaryVolunteerId): array
    {
        // We get the Volunteer and we check if it's really a volunteer
        $solidaryVolunteer = $this->solidaryUserRepository->find($solidaryVolunteerId);
        
        if (!$solidaryVolunteer->isVolunteer()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_VOLUNTEER);
        }
        
        $solidaryAsks = $this->solidaryAskRepository->findBetweenTwoDates($startDate, $endDate, $solidaryVolunteer);

        $fullPlanning = [];
        $currentDate = $startDate;
        // We make the schedule. Day by day.
        while ($currentDate<=$endDate) {
            $solidaryVolunteerPlanning = new SolidaryVolunteerPlanning();
            $solidaryVolunteerPlanning->setDate($currentDate);
            
            // We check if we found a solidaryAsk for this day
            foreach ($solidaryAsks as $solidaryAsk) {
                /**
                 * @var SolidaryAsk $solidaryAsk
                 */
                if (
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_PUNCTUAL && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")==$currentDate->format("d/m/Y")) ||
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_REGULAR && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")<=$currentDate->format("d/m/Y") && $solidaryAsk->getCriteria()->getToDate()->format("d/m/Y")>=$currentDate->format("d/m/Y"))
                ) {

                    // Determine the hour slot
                    $structure = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getStructure();
                    $slot = $this->solidaryMatcher->getHourSlot($solidaryAsk->getCriteria()->getFromTime(), $solidaryAsk->getCriteria()->getFromTime(), $structure);

                    $solidaryVolunteerPlanningItem = new SolidaryVolunteerPlanningItem();

                    // The beneficiary
                    $beneficiary = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
                    $solidaryVolunteerPlanningItem->setBeneficiary($beneficiary->getGivenName()." ".$beneficiary->getFamilyName());


                    // different usefull ids
                    $solidaryVolunteerPlanningItem->setSolidaryId($solidaryAsk->getSolidarySolution()->getSolidary()->getId());
                    $solidaryVolunteerPlanningItem->setSolidarySolutionId($solidaryAsk->getSolidarySolution()->getId());

                    // status
                    $solidaryVolunteerPlanningItem->setStatus($solidaryAsk->getStatus());

                    switch ($slot) {
                        case 'm': $solidaryVolunteerPlanning->setMorningSlot($solidaryVolunteerPlanningItem);break;
                        case 'a': $solidaryVolunteerPlanning->setAfternoonSlot($solidaryVolunteerPlanningItem);break;
                        case 'e': $solidaryVolunteerPlanning->setEveningSlot($solidaryVolunteerPlanningItem);break;
                    }
                }
            }

            $fullPlanning[] = $solidaryVolunteerPlanning;
            
            $currentDate = clone $currentDate;
            $currentDate->modify('+1 day');
        }

        return $fullPlanning;
    }
}
