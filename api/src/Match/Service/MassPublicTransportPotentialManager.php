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
use App\Geography\Service\GeoTools;
use App\Match\Entity\MassPTJourney;
use App\Match\Event\MassPublicTransportSolutionsGatheredEvent;
use App\Match\Repository\MassPTJourneyRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private $massPTJourneyRepository;
    private $geoTools;
    private $params;
    private $eventDispatcher;

    private const ROUND_TRIP_COMPUTE = true; // Multiply the computed numbers by two

    public function __construct(
        MassRepository $massRepository,
        PTDataProvider $pTDataProvider,
        EntityManagerInterface $entityManager,
        MassPTJourneyRepository $massPTJourneyRepository,
        GeoTools $geoTools,
        EventDispatcherInterface $eventDispatcher,
        array $params
    ) {
        $this->massRepository = $massRepository;
        $this->pTDataProvider = $pTDataProvider;
        $this->entityManager = $entityManager;
        $this->massPTJourneyRepository = $massPTJourneyRepository;
        $this->geoTools = $geoTools;
        $this->eventDispatcher = $eventDispatcher;
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

        if ($mass->getStatus() < 4) {
            throw new MassException(MassException::MASS_NOT_ANALYZED);
        }

        // Update the gettingPublicTransportationPotentialDate
        $mass->setGettingPublicTransportationPotentialDate(new \DateTime());
        $this->entityManager->flush();

        // We remove the previous PTJourneys
        $this->massPTJourneyRepository->deleteMassPTJourneysOfAMass($id);

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
            // var_dump($results);die;
            foreach ($results as $ptjourney) {
                $massPTJourney = $this->buildMassPTJourney($ptjourney);
                $massPTJourney->setMassPerson($person);
                $TPPotential[] = $massPTJourney;

                // We persist the MassPTJourney
                $this->entityManager->persist($massPTJourney);
            }
        }

        // Update the gotPublicTransportationPotentialDate
        $mass->setGotPublicTransportationPotentialDate(new \DateTime());

        $this->entityManager->flush();

        $mass->setPublicTransportPotential($TPPotential);

        // Send an email to the operator
        $event = new MassPublicTransportSolutionsGatheredEvent($mass);
        $this->eventDispatcher->dispatch(MassPublicTransportSolutionsGatheredEvent::NAME, $event);

        return $mass;
    }

    
    /**
     * Build the MassPTJourney from a PTJourney
     *
     * @param PTJourney $ptjourney  The PTJourney
     * @return MassPTJourney
     */
    public function buildMassPTJourney(PTJourney $ptjourney): MassPTJourney
    {
        $massPTJourney = new MassPTJourney();
        
        $massPTJourney->setDuration($ptjourney->getDuration());

        $massPTJourney->setDistance($ptjourney->getDistance());

        // Duration from home
        $legFromHome = $ptjourney->getPTLegs()[0];
        $durationFromHome = $legFromHome->getDuration();
        $massPTJourney->setDurationWalkFromHome($durationFromHome);


        // Distance from home
        $distanceFromHome = 4000 * $durationFromHome / 3600;
        $massPTJourney->setDistanceWalkFromHome($distanceFromHome);

        // Duration from Work
        $legFromWork = $ptjourney->getPTLegs()[count($ptjourney->getPTLegs())-1];
        $durationFromWork = $legFromWork->getDuration();
        $massPTJourney->setDurationWalkFromWork($durationFromWork);

        // Distance from Work
        $distanceFromWork = 4000 * $durationFromWork / 3600;
        $massPTJourney->setDistanceWalkFromWork($distanceFromWork);

        // Number of changes
        $massPTJourney->setChangeNumber($ptjourney->getChangeNumber());

        return $massPTJourney;
    }
     

    /**
     * Check if a MassPTJourney is valid for public transport potential
     * @var MassPTJourney $pTPotentialJourney The potential journey to check
     * @return boolean
     */
    public function checkValidMassPTJourney(MassPTJourney $massPTJourney): bool
    {
        // Number of connections
        if ($massPTJourney->getChangeNumber() > $this->params['ptMaxConnections']) {
            return false;
        }
        
        // The maximum distance of walk from home to the last step
        if ($massPTJourney->getDistanceWalkFromHome()> $this->params['ptMaxDistanceWalkFromHome']) {
            return false;
        }

        // The maximum distance of walk to work from the last step
        if ($massPTJourney->getDistanceWalkFromWork()> $this->params['ptMaxDistanceWalkFromWork']) {
            return false;
        }

        // The maximum duration of PT journey must be < xN the duration in car
        if ($massPTJourney->getDuration() > ($massPTJourney->getMassPerson()->getDuration()*$this->params['ptMaxNbCarDuration'])) {
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

        $computedData = [
            "totalPerson" => count($persons),
            "totalPTSolutions" => 0,
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
            "humanReadableSavedDuration" => "",
            "criteria" => [
                "ptMaxConnections" => $this->params['ptMaxConnections'],
                "ptMaxDistanceWalkFromHome" => $this->params['ptMaxDistanceWalkFromHome'],
                "ptMaxDistanceWalkFromWork" => $this->params['ptMaxDistanceWalkFromWork'],
                "ptMaxNbCarDuration" => $this->params['ptMaxNbCarDuration']
            ]
        ];

        foreach ($persons as $person) {
            
            // Original travel
            if (count($person->getMassPTJourneys())>0) {
                $ptjourneys = $person->getMassPTJourneys();
                $computedData['totalPTSolutions'] += count($person->getMassPTJourneys());

                foreach ($ptjourneys as $ptjourney) {
                    if ($this->checkValidMassPTJourney($ptjourney)) {
                        $computedData['totalPersonWithValidPTSolution']++;

                        $computedData["totalTravelDistance"] += $person->getDistance();
                        $computedData["totalTravelDuration"] += $person->getDuration();

                        

                        $computedData['totalPTDistance'] += $ptjourney->getDistance();
                        $computedData['totalPTDuration'] += $ptjourney->getDuration();
                        break;
                    }
                }
            }
        }

        // CO2 consumption of original travel
        $computedData["totalTravelDistanceCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistance"]);

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
        $computedData['savedCO2PerYear'] = $computedData["savedCO2"] * Mass::NB_WORKING_DAY;
        $computedData["savedDurationByCarPerYear"] = $computedData["savedDurationByCar"] * Mass::NB_WORKING_DAY;
        $computedData["savedDistanceByCarPerYear"] = $computedData["savedDistanceByCar"] * Mass::NB_WORKING_DAY;

        $computedData["totalTravelDistancePerYearCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistancePerYear"]);

        if ($this->params['roundTripCompute']) {
            $computedData["totalTravelDistanceCO2"] *= 2;
            $computedData['savedCO2'] *= 2;
            $computedData['savedDurationByCar'] *= 2;
            $computedData['savedDistanceByCar'] *= 2;
            $computedData["totalTravelDistancePerYear"] *= 2;
            $computedData["totalTravelDurationPerYear"] *= 2;
            $computedData['savedCO2PerYear'] *= 2;
            $computedData["savedDurationByCarPerYear"] *= 2;
            $computedData["savedDistanceByCarPerYear"] *= 2;
        
            $computedData['roundtripComputed'] = true;
        } else {
            $computedData['roundtripComputed'] = false;
        }
        

        $mass->setPublicTransportPotential($computedData);

        return $mass;
    }
}
