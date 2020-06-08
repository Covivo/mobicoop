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
use App\Travel\Entity\TravelMode;
use App\PublicTransport\Entity\PTLeg;
use App\PublicTransport\Entity\PTPotentialJourney;
use DateInterval;

/**
 * Mass public transport potential manager.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassPublicTransportPotentialManager
{
    private $massRepository;
    private $pTDataProvider;
    private $params;

    public function __construct(MassRepository $massRepository, PTDataProvider $pTDataProvider, array $params)
    {
        $this->massRepository = $massRepository;
        $this->pTDataProvider = $pTDataProvider;
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
            
            foreach ($results as $result) {
                $PTPotentialJourney = $this->buildPTPotentialJourney($person, $result);
                if ($this->checkValidPTJourney($PTPotentialJourney)) {
                    $TPPotential[$person->getId()][] = $PTPotentialJourney;
                }
            }
        }

        $mass->setPublicTransportPotential($TPPotential);
        return $mass;
    }

    
    
    /**
     * Build a PTPotentialJourney from a PTJourney
     *
     * @param MassPerson $person    The owner of the journey
     * @param PTJourney $pTJourney  The journey we build the potential journey from
     * @return PTPotentialJourney
     */
    public function buildPTPotentialJourney(MassPerson $person, PTJourney $pTJourney): PTPotentialJourney
    {
        $PTPotentialJourney = new PTPotentialJourney(time());

        $PTPotentialJourney->setMassPerson($person);
        $interval = new DateInterval($pTJourney->getDuration());
        $duration = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $PTPotentialJourney->setDuration($duration);
        $PTPotentialJourney->setDistance($pTJourney->getDistance());
        $PTPotentialJourney->setChangeNumber($pTJourney->getChangeNumber());
        $PTPotentialJourney->setCo2($pTJourney->getCo2());

        // Duration from home
        $legFromHome = $pTJourney->getPTLegs()[0];
        $interval = new DateInterval($legFromHome->getDuration());
        $durationFromHome = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $PTPotentialJourney->setDurationWalkFromHome($durationFromHome);


        // Distance from home
        $distanceFromHome = 4000 * $durationFromHome / 3600;
        $PTPotentialJourney->setDistanceWalkFromHome($distanceFromHome);

        // Duration from Work
        $legFromWork = $pTJourney->getPTLegs()[count($pTJourney->getPTLegs())-1];
        $interval = new DateInterval($legFromWork->getDuration());
        $durationFromWork = (new \DateTime())->setTimeStamp(0)->add($interval)->getTimeStamp();
        $PTPotentialJourney->setDurationWalkFromWork($durationFromWork);

        // Distance from Work
        $distanceFromWork = 4000 * $durationFromWork / 3600;
        $PTPotentialJourney->setDistanceWalkFromWork($distanceFromWork);




        return $PTPotentialJourney;
    }
    
    
    /**
     * Check if a PTPotentialJourney is valid for public transport potential
     * @var PTPotentialJourney $pTPotentialJourney The potential journey to check
     * @return boolean
     */
    public function checkValidPTJourney(PTPotentialJourney $pTPotentialJourney): bool
    {
        // Number of connections
        if ($pTPotentialJourney->getChangeNumber() > $this->params['ptMaxConnections']) {
            return false;
        }
        
        // The maximum distance of walk from home to the last step
        if ($pTPotentialJourney->getDistanceWalkFromHome()> $this->params['ptMaxDistanceWalkFromHome']) {
            return false;
        }

        // The maximum distance of walk to work from the last step
        if ($pTPotentialJourney->getDistanceWalkFromWork()> $this->params['ptMaxDistanceWalkFromWork']) {
            return false;
        }

        return true;
    }
}
