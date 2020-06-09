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
use App\Match\Entity\MassJourney;
use App\Match\Entity\MassMatrix;
use App\Match\Entity\MassPerson;
use App\Match\Exception\MassException;
use App\Match\Repository\MassRepository;
use App\PublicTransport\Entity\PTJourney;
use App\PublicTransport\Service\PTDataProvider;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\PublicTransport\Repository\PTJourneyRepository;
use App\Geography\Service\GeoTools;

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
    private $pTJourneyRepository;
    private $geoTools;
    private $params;

    public function __construct(
        MassRepository $massRepository,
        PTDataProvider $pTDataProvider,
        EntityManagerInterface $entityManager,
        PTJourneyRepository $pTJourneyRepository,
        GeoTools $geoTools,
        array $params
    ) {
        $this->massRepository = $massRepository;
        $this->pTDataProvider = $pTDataProvider;
        $this->entityManager = $entityManager;
        $this->pTJourneyRepository = $pTJourneyRepository;
        $this->geoTools = $geoTools;
        $this->params = $params;
    }

    /**
     * Get the public transport potential of a Mass from a PT Api
     *
     * @param integer $id   Id of the Mass
     * @return Mass
     */
    public function getPublicTransportPotential(int $id): Mass
    {
        $mass = $this->massRepository->find($id);

        // Update the gettingPublicTransportationPotentialDate
        $mass->setGettingPublicTransportationPotentialDate(new \DateTime());
        $this->entityManager->flush();

        // We remove the previous PTJourneys
        $this->pTJourneyRepository->deletePTJourneysOfAMass($id);

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

        // Update the gotPublicTransportationPotentialDate
        $mass->setGotPublicTransportationPotentialDate(new \DateTime());

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

    /**
     * Compute the public transport potential of a Mass from a PT Api
     *
     * @param integer $id   Id of the Mass
     * @return Mass         The mass with the publicTransportPotential property filled
     */
    public function computePublicTransportPotential(int $id): Mass
    {
        $mass = $this->massRepository->find($id);
        
        if (is_null($mass->getPersons())) {
            throw new MassException(MassException::NO_MASSPERSON);
        }

        // Total person of this Mass
        $persons = $mass->getPersons();

        $matrix = new MassMatrix();

        $computedData = [
            "totalPerson" => count($persons),
            "totalPersonWithValidPTSolution" => 0,
            "PTPotential" => 0,
            "totalTravelDistance" => 0,
            "totalPTDistance" => 0,
            "totalTravelDistanceCO2" => 0,
            "totalTravelDistancePerYear" => 0,
            "totalTravelDistancePerYearCO2" => 0,
            "totalTravelDuration" => 0,
            "totalPTDuration" => 0,
            "totalTravelDurationPerYear" => 0,
            "savedDistanceByCar" => 0,
            "savedDurationByCar" => 0,
            "savedDistanceByCarPerYear" => 0,
            "savedDurationByCarPerYear" => 0,
            "savedCO2" => 0,
            "savedCO2PerYear" => 0,
            "humanReadableSavedDuration" => ""
        ];

        foreach ($persons as $person) {
            
            // Original travel
            $computedData["totalTravelDistance"] += $person->getDistance();
            $computedData["totalTravelDuration"] += $person->getDuration();

            $ptjourney = $person->getPtJourneys()[0];

            $computedData['totalPTDistance'] += $ptjourney->getDistance();
            $computedData['totalPTDuration'] += $ptjourney->getDurationInSeconds();

            if (count($person->getPTjourneys())>0) {
                $computedData['totalPersonWithValidPTSolution']++;
            }

            // Store the original journey to calculate the gains between original and carpool
            if ($mass->getStatus()==Mass::STATUS_MATCHED && $person->getDistance()!==null) {
                // Only if the analyse has been done.
                $journey = new MassJourney(
                    $person->getDistance(),
                    $person->getDuration(),
                    $this->geoTools->getCO2($person->getDistance()),
                    $person->getId()
                );
                $matrix->addOriginalsJourneys($journey);
            }
        }


        // CO2 consumption of original travel
        $computedData["totalTravelDistanceCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistance"]);
        $computedData["totalTravelDistancePerYearCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistancePerYear"]);

        // PT Potential
        $computedData['PTPotential'] = $computedData['totalPersonWithValidPTSolution'] / $computedData['totalPerson'] * 100;

        // Co2 saved
        // It's a percentage of the total CO2 of a regular travel by car using the PT Potential.
        // We assume that PT travel consume 0 Co2
        $computedData['savedCO2'] = $computedData["totalTravelDistanceCO2"] * $computedData['PTPotential'] / 100;

        // Distance and Duration saved
        // It's a percentage of the total distance and duration of a regular travel by car using the PT Potential.
        // It's the distance and duration that are not made by car.
        $computedData['savedDurationByCar'] = $computedData["totalTravelDuration"] * $computedData['PTPotential'] / 100;
        $computedData['savedDistanceByCar'] = $computedData["totalTravelDistance"] * $computedData['PTPotential'] / 100;
        
        // Per year
        $computedData["totalTravelDistancePerYear"] = $computedData["totalTravelDistance"] * Mass::NB_WORKING_DAY;
        $computedData["totalTravelDurationPerYear"] = $computedData["totalTravelDuration"] * Mass::NB_WORKING_DAY;
        $computedData['savedCO2PerYear'] = $computedData["savedCO2PerYear"] * Mass::NB_WORKING_DAY;
        $computedData["savedDurationByCarPerYear"] = $computedData["savedDurationByCar"] * Mass::NB_WORKING_DAY;
        $computedData["savedDistanceByCarPerYear"] = $computedData["savedDistanceByCar"] * Mass::NB_WORKING_DAY;


        $mass->setPublicTransportPotential($computedData);

        return $mass;
    }
}
