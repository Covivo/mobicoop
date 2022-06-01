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
use App\Carpool\Entity\Criteria;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\Solidary\Entity\SolidaryFormalRequest;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidarySolutionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidarySolutionManager
{
    private $entityManager;
    private $security;
    private $solidarySolutionRepository;

    public function __construct(EntityManagerInterface $entityManager, Security $security, SolidarySolutionRepository $solidarySolutionRepository)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->solidarySolutionRepository = $solidarySolutionRepository;
    }

    /**
     * Create a SolidarySolution.
     */
    public function createSolidarySolution(SolidarySolution $solidarySolution): ?SolidarySolution
    {
        // If the solidaryMatching have already a solidarySolution we throw an error because a solidaryMatching can only have ONE solidarySolution
        if (!is_null($solidarySolution->getSolidaryMatching()->getSolidarySolution())) {
            throw new SolidaryException(SolidaryException::SOLIDARY_MATCHING_ALREADY_USED);
        }
        // If there is a SolidaryUser, it has to be a volunteer
        if (!is_null($solidarySolution->getSolidaryMatching()->getSolidaryUser()) && !$solidarySolution->getSolidaryMatching()->getSolidaryUser()->isVolunteer()) {
            throw new SolidaryException(SolidaryException::IS_NOT_VOLUNTEER);
        }
        // Can't have both matching et solidaryUser
        if (!is_null($solidarySolution->getSolidaryMatching()->getSolidaryUser()) && !is_null($solidarySolution->getSolidaryMatching()->getMatching())) {
            throw new SolidaryException(SolidaryException::CANT_HAVE_BOTH);
        }

        // We get the Solidary of this SolidaryMatching and set it for the SolidarySolution ((yeah, it a shortcut for the model)
        $solidarySolution->setSolidary($solidarySolution->getSolidaryMatching()->getSolidary());

        $this->entityManager->persist($solidarySolution);
        $this->entityManager->flush();

        return $solidarySolution;
    }

    /**
     * Make a formal request for a SolidarySolution.
     *
     * @param SolidaryFormalRequest $solidarySolution
     */
    public function makeFormalRequest(SolidaryFormalRequest $solidaryFormalRequest): ?SolidaryFormalRequest
    {
        $solidarySolution = $solidaryFormalRequest->getSolidarySolution();

        // Get the solidaryAsk
        // Check if there is a SolidaryAsk
        $solidaryAsk = $solidarySolution->getSolidaryAsk();
        if (is_null($solidaryAsk)) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_ASK);
        }

        // Check if the SolidaryAsk has the right status
        if (SolidaryAsk::STATUS_ASKED !== $solidaryAsk->getStatus()) {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARY_ASK_STATUS_FOR_FORMAL);
        }

        // Update the SolidaryAsk Criteria
        $solidaryAskCriteria = $this->updateCriteriaFromFormalRequest($solidaryFormalRequest, $solidaryAsk->getCriteria());
        $this->entityManager->persist($solidaryAskCriteria);
        $this->entityManager->flush();

        //  Update the status of the SolidaryAsk and add a SolidaryAskHistory
        $solidaryAsk->setStatus(SolidaryAsk::STATUS_PENDING);
        $this->entityManager->persist($solidaryAsk);
        $this->entityManager->flush();
        $solidaryAskHistory = new SolidaryAskHistory();
        $solidaryAskHistory->setStatus($solidaryAsk->getStatus());
        $solidaryAskHistory->setSolidaryAsk($solidaryAsk);
        $this->entityManager->persist($solidaryAskHistory);
        $this->entityManager->flush();

        // If it's a Carpool Ask, we need to treat also the real Ask
        // Ask Criteria if it exists
        if (!is_null($solidaryAsk->getAsk())) {
            // Update the Criteria
            $askCriteria = $this->updateCriteriaFromFormalRequest($solidaryFormalRequest, $solidaryAsk->getAsk()->getCriteria());
            $this->entityManager->persist($askCriteria);
            $this->entityManager->flush();

            //  Update the status of the Ask and add a AskHistory
            $ask = $solidaryAsk->getAsk();
            $ask->setStatus(Ask::STATUS_PENDING_AS_PASSENGER);
            $this->entityManager->persist($ask);
            $this->entityManager->flush();
            $askHistory = new AskHistory();
            $askHistory->setStatus($ask->getStatus());
            $askHistory->setType($ask->getType());
            $askHistory->setAsk($ask);
            $this->entityManager->persist($askHistory);
            $this->entityManager->flush();

            // If there is an Ask linked we update it (the return)
            if ($solidaryAsk->getAsk()->getAskLinked()) {
                $askLinked = $solidaryAsk->getAsk()->getAskLinked();

                // Update the Criteria
                $askLinkedCriteria = $this->updateCriteriaFromFormalRequest($solidaryFormalRequest, $askLinked->getCriteria(), 'return');
                $this->entityManager->persist($askLinkedCriteria);
                $this->entityManager->flush();

                // For now, we don't handle AskHistories on AskLinked. If we do, we need to write the code here
            }
        }

        return $solidaryFormalRequest;
    }

    /**
     * Get a SolidaryFormalRequest.
     *
     * @param int $solidarySolutionId The SolidarySolutionId the SolidaryFormalRequest is based on
     */
    public function getSolidaryFormalRequest(int $solidarySolutionId): SolidaryFormalRequest
    {
        $solidarySolution = $this->solidarySolutionRepository->find($solidarySolutionId);

        if (is_null($solidarySolution)) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_SOLUTION);
        }

        $solidaryFormalRequest = new SolidaryFormalRequest();
        $solidaryFormalRequest->setSolidarySolution($solidarySolution);

        // We get the good criteria. If there is a solidaryAsk, we take it, if not, we take the Proposal Criteria
        $criteria = $criteriaReturn = null;
        if (!is_null($solidarySolution->getSolidaryAsk())) {
            $criteria = $solidarySolution->getSolidaryAsk()->getCriteria();
            // Return ? Only carpool
            if (!is_null($solidarySolution->getSolidaryAsk()->getAsk()) && !is_null($solidarySolution->getSolidaryAsk()->getAsk()->getAskLinked())) {
                $criteriaReturn = $solidarySolution->getSolidaryAsk()->getAsk()->getAskLinked()->getCriteria();
            }
        } else {
            $criteria = $solidarySolution->getSolidary()->getProposal()->getCriteria();
            if (!is_null($solidarySolution->getSolidary()->getProposal()->getProposalLinked())) {
                $criteriaReturn = $solidarySolution->getSolidary()->getProposal()->getProposalLinked()->getCriteria();
            }
        }

        // Dates
        $solidaryFormalRequest->setOutwardDate($criteria->getFromDate());

        $solidaryFormalRequest->setOutwardLimitDate($criteria->getFromDate());
        if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
            $solidaryFormalRequest->setOutwardLimitDate($criteria->getToDate());

            // Days
            $solidaryFormalRequest->setOutwardSchedule($this->buildScheduleFromCriteria($criteria, 'outward'));
        } else {
            // For a punctual, we generate only with one day
            $outwardSchedule[] = [
                'outwardTime' => $criteria->getFromTime()->format('H:i'),
                lcfirst($criteria->getFromDate()->format('D')) => 1,
            ];
            $solidaryFormalRequest->setOutwardSchedule($outwardSchedule);
        }

        // Return dates and schedule ? Only carpool
        if (!is_null($criteriaReturn)) {
            // Dates
            $solidaryFormalRequest->setReturnDate($criteriaReturn->getFromDate());
            $solidaryFormalRequest->setReturnLimitDate($criteriaReturn->getFromDate());
            if (Criteria::FREQUENCY_REGULAR == $criteriaReturn->getFrequency()) {
                $solidaryFormalRequest->setReturnLimitDate($criteriaReturn->getToDate());

                // Days
                $solidaryFormalRequest->setReturnSchedule($this->buildScheduleFromCriteria($criteriaReturn, 'return'));
            } else {
                // For a punctual, we generate only with one day
                $returnSchedule[] = [
                    'returnTime' => $criteriaReturn->getFromTime()->format('H:i'),
                    lcfirst($criteriaReturn->getFromDate()->format('D')) => 1,
                ];
                $solidaryFormalRequest->setReturnSchedule($returnSchedule);
            }
        }

        return $solidaryFormalRequest;
    }

    private function buildScheduleFromCriteria($criteria, $way)
    {
        $schedule = [];
        if ($criteria->isMonCheck() && !is_null($criteria->getMonTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'mon', $criteria->getMonTime()->format('H:i'), $way);
        }
        if ($criteria->isTueCheck() && !is_null($criteria->getTueTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'tue', $criteria->getTueTime()->format('H:i'), $way);
        }
        if ($criteria->isWedCheck() && !is_null($criteria->getWedTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'wed', $criteria->getWedTime()->format('H:i'), $way);
        }
        if ($criteria->isThuCheck() && !is_null($criteria->getThuTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'thu', $criteria->getThuTime()->format('H:i'), $way);
        }
        if ($criteria->isFriCheck() && !is_null($criteria->getFriTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'fri', $criteria->getFriTime()->format('H:i'), $way);
        }
        if ($criteria->isSatCheck() && !is_null($criteria->getSatTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'sat', $criteria->getSatTime()->format('H:i'), $way);
        }
        if ($criteria->isSunCheck() && !is_null($criteria->getSunTime())) {
            $schedule = $this->buildDaySchedule($schedule, 'sun', $criteria->getSunTime()->format('H:i'), $way);
        }

        return $schedule;
    }

    private function buildDaySchedule($schedule, $day, $time, $way)
    {
        $found = false;
        foreach ($schedule as $key => $currentSchedule) {
            if ($currentSchedule[$way.'Time'] == $time) {
                $schedule[$key][$day] = 1;
                $found = true;

                break;
            }
        }
        if (!$found) {
            $newSchedule = ['mon' => 0, 'tue' => 0, 'wed' => 0, 'thu' => 0, 'fri' => 0, 'sat' => 0, 'sun' => 0];
            $newSchedule[$way.'Time'] = $time;
            $newSchedule[$day] = 1;
            $schedule[] = $newSchedule;
        }

        return $schedule;
    }

    /**
     * Update a Criteria based on the SolidaryFormalRequest data.
     *
     * @param SolidaryFormalRequest $solidaryFormalRequest The solidary formal request
     * @param Criteria              $criteria              The Criteria to update
     * @param string                $way                   Outward or Return
     *
     * @return Criteria The updated Criteria
     */
    private function updateCriteriaFromFormalRequest(SolidaryFormalRequest $solidaryFormalRequest, Criteria $criteria, string $way = 'outward'): Criteria
    {
        $criteria->setFromDate($solidaryFormalRequest->getOutwardDate());

        // Treat the schedule
        $schedules = $solidaryFormalRequest->getOutwardSchedule();

        foreach ($schedules as $schedule) {
            if (isset($schedule['mon']) && 1 == $schedule['mon']) {
                $criteria->setMonCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setMonTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['tue']) && 1 == $schedule['tue']) {
                $criteria->setTueCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setTueTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['wed']) && 1 == $schedule['wed']) {
                $criteria->setWedCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setWedTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['thu']) && 1 == $schedule['thu']) {
                $criteria->setThuCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setThuTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['fri']) && 1 == $schedule['fri']) {
                $criteria->setFriCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setFriTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['sat']) && 1 == $schedule['sat']) {
                $criteria->setSatCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setSatTime(new \DateTime($schedule[$way.'Time']));
                }
            }
            if (isset($schedule['sun']) && 1 == $schedule['sun']) {
                $criteria->setSunCheck(true);
                if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
                    $criteria->setSunTime(new \DateTime($schedule[$way.'Time']));
                }
            }
        }
        // The toDate is only for regular
        if (Criteria::FREQUENCY_REGULAR == $criteria->getFrequency()) {
            $criteria->setToDate($solidaryFormalRequest->getOutwardLimitDate());
        } else {
            // Punctual journey we update fromTime
            $criteria->setFromTime(new \DateTime($schedules[0][$way.'Time']));
        }

        return $criteria;
    }
}
