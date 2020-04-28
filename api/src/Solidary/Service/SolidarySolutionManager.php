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
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\Solidary\Entity\SolidaryFormalRequest;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryMatchingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class SolidarySolutionManager
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * Create a SolidarySolution
     *
     * @param SolidarySolution $solidarySolution
     * @return SolidarySolution|null
     */
    public function createSolidarySolution(SolidarySolution $solidarySolution): ?SolidarySolution
    {
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
     * Make a formal request for a SolidarySolution
     *
     * @param SolidaryFormalRequest $solidarySolution
     * @return SolidaryFormalRequest|null
     */
    public function makeFormalRequest(SolidaryFormalRequest $solidaryFormalRequest) : ?SolidaryFormalRequest
    {
        $solidarySolution = $solidaryFormalRequest->getSolidarySolution();

        /*****  Update the criteria of the SolidaryAsk */

        // Get the solidaryAsk
        
        // Check if there is a SolidaryAsk
        $solidaryAsk = $solidarySolution->getSolidaryAsk();
        if (is_null($solidaryAsk)) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_ASK);
        }

        // Check if the SolidaryAsk has the right status
        if ($solidaryAsk->getStatus()!==SolidaryAsk::STATUS_ASKED) {
            throw new SolidaryException(SolidaryException::BAD_SOLIDARY_ASK_STATUS_FOR_FORMAL);
        }

        // SolidaryAsk Criteria
        $solidaryAskCriteria = $solidaryAsk->getCriteria();

        $solidaryAskCriteria->setFromDate($solidaryFormalRequest->getOutwardDate());
        // TO DO : RETURN FOR CARPOOL

        // Treat the schedule
        $outwardSchedule = $solidaryFormalRequest->getOutwardSchedule()[0];
        if (isset($outwardSchedule["mon"]) && $outwardSchedule["mon"]==1) {
            $solidaryAskCriteria->setMonCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setMonTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["tue"]) && $outwardSchedule["tue"]==1) {
            $solidaryAskCriteria->setTueCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setTueTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["wed"]) && $outwardSchedule["wed"]==1) {
            $solidaryAskCriteria->setWedCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setWedTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["thu"]) && $outwardSchedule["thu"]==1) {
            $solidaryAskCriteria->setThuCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setThuTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["fri"]) && $outwardSchedule["fri"]==1) {
            $solidaryAskCriteria->setFriCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setFriTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["sat"]) && $outwardSchedule["sat"]==1) {
            $solidaryAskCriteria->setSatCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setSatTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }
        if (isset($outwardSchedule["sun"]) && $outwardSchedule["sun"]==1) {
            $solidaryAskCriteria->setSunCheck(true);
            if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                $solidaryAskCriteria->setSunTime(new \DateTimeInterface($outwardSchedule['outwardTime']));
            }
        }

        // The toDate is only for regular
        if ($solidaryAskCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
            $solidaryAskCriteria->setToDate($solidaryFormalRequest->getOutwardLimitDate());
        } else {
            // Punctual journey we update fromTime
            $solidaryAskCriteria->setFromTime(new \DateTime($outwardSchedule['outwardTime']));
        }

        $this->entityManager->persist($solidaryAskCriteria);
        $this->entityManager->flush();



        /*****  Update the status of the SolidaryAsk and add a SolidaryAsk history */
        $solidaryAsk->setStatus(SolidaryAsk::STATUS_PENDING);
        $this->entityManager->persist($solidaryAsk);
        $this->entityManager->flush();
        $solidaryAskHistory = new SolidaryAskHistory();
        $solidaryAskHistory->setStatus($solidaryAsk->getStatus());
        $solidaryAskHistory->setSolidaryAsk($solidaryAsk);
        $this->entityManager->persist($solidaryAskHistory);
        $this->entityManager->flush();

        /*****  If this is a Carpool Solidary Solution, we need to update the carpool Ask and its Criteria */


        return $solidaryFormalRequest;
    }
}
