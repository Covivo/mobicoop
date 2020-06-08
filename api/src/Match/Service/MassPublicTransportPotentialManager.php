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

namespace App\Match\Service;

use App\Match\Entity\Mass;
use App\Match\Entity\MassPerson;
use App\Match\Repository\MassRepository;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Service\PTDataProvider;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Mass public transport potential manager.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassPublicTransportPotentialManager
{
    private $massRepository;
    private $pTDataProvider;
    private $entityManager;
    private $params;

    public function __construct(MassRepository $massRepository, PTDataProvider $pTDataProvider, EntityManagerInterface $entityManager, array $params)
    {
        $this->massRepository = $massRepository;
        $this->pTDataProvider = $pTDataProvider;
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    /**
     * Compute the public transport potential of a Mass
     *
     * @param integer $id   Id of the Mass
     * @return Mass
     */
    public function computePublicTransportPotential(int $id): Mass
    {
        $mass = $this->massRepository->find($id);

        $TPPotential = [];
        foreach ($mass->getPersons() as $person) {
            /**
             * @var MassPerson $person
             */
            $results = $this->pTDataProvider->getJourneys(
                $this->params['ptProvider'],
                1,
                $person->getPersonalAddress()->getLatitude(),
                $person->getPersonalAddress()->getLongitude(),
                $person->getWorkAddress()->getLatitude(),
                $person->getWorkAddress()->getLongitude(),
                new \DateTime(Date("Y-m-d").' '.$person->getOutwardTime()->format("H:i:s"), new \DateTimeZone('Europe/Paris')),
                "departure",
                $this->params['ptAlgorithm'],
                "PT"
            );
            
            foreach ($results as $ptjourney) {
                $PTPotentialJourney = $this->computeDataPTJourney($ptjourney);
                if ($this->checkValidPTJourney($PTPotentialJourney)) {
                    $ptjourney->setMassPerson($person);
                    $TPPotential[] = $PTPotentialJourney;

                    // For now, we don't want to persist PTDeparture, PTArrival and PTLeg
                    $ptjourney->setPTLegs(new ArrayCollection());
                    $ptjourney->setPTDeparture(null);
                    $ptjourney->setPTArrival(null);

                    
                    // We persist the PTJourney
                    $this->entityManager->persist($ptjourney);
                }
            }
        }

        $this->entityManager->flush();

        $mass->setPublicTransportPotential($TPPotential);

        return $mass;
    }

    
    /**
     * Compute the complex data of a PTJourney
     *
     * @param PTJourney $ptjourney  The PTJourney
     * @return PTJourney
     */
    public function computeDataPTJourney(PTJourney $ptjourney): PTJourney
    {
        $interval = new DateInterval($ptjourney->getDuration());
        $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $ptjourney->setDurationInSeconds($duration);


        // Duration from home
        $legFromHome = $ptjourney->getPTLegs()[0];
        $interval = new DateInterval($legFromHome->getDuration());
        $durationFromHome = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $ptjourney->setDurationWalkFromHome($durationFromHome);


        // Distance from home
        $distanceFromHome = 4000 * $durationFromHome / 3600;
        $ptjourney->setDistanceWalkFromHome($distanceFromHome);

        // Duration from Work
        $legFromWork = $ptjourney->getPTLegs()[count($ptjourney->getPTLegs())-1];
        $interval = new DateInterval($legFromWork->getDuration());
        $durationFromWork = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $ptjourney->setDurationWalkFromWork($durationFromWork);

        // Distance from Work
        $distanceFromWork = 4000 * $durationFromWork / 3600;
        $ptjourney->setDistanceWalkFromWork($distanceFromWork);

        return $ptjourney;
    }
     

    /**
     * Check if a PTPotentialJourney is valid for public transport potential
     * @var PTJourney $pTPotentialJourney The potential journey to check
     * @return boolean
     */
    public function checkValidPTJourney(PTJourney $ptjourney): bool
    {
        // Number of connections
        if ($ptjourney->getChangeNumber() > $this->params['ptMaxConnections']) {
            return false;
        }
        
        // The maximum distance of walk from home to the last step
        if ($ptjourney->getDistanceWalkFromHome()> $this->params['ptMaxDistanceWalkFromHome']) {
            return false;
        }

        // The maximum distance of walk to work from the last step
        if ($ptjourney->getDistanceWalkFromWork()> $this->params['ptMaxDistanceWalkFromWork']) {
            return false;
        }

        return true;
    }
}
