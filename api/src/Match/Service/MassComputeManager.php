<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use App\Geography\Service\GeoTools;
use App\Match\Entity\Mass;
use App\Match\Entity\MassMatrix;
use App\Match\Entity\MassJourney;
use App\Match\Entity\MassCarpool;
use App\Match\Entity\MassPerson;
use App\Match\Repository\MassPersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use App\Service\FormatDataManager;

/**
 * Mass compute manager.
 *
 * This service compute all Masses data.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassComputeManager
{
    private $formatDataManager;
    private $geoTools;
    private $massPersonRepository;

    public function __construct(FormatDataManager $formatDataManager, GeoTools $geoTools, MassPersonRepository $massPersonRepository)
    {
        $this->formatDataManager = $formatDataManager;
        $this->geoTools = $geoTools;
        $this->massPersonRepository = $massPersonRepository;
    }

    /**
     * Compute all necessary calculations for a mass
     *
     * @return Mass
     */
    public function computeResults(Mass $mass)
    {
        $computedData = [
            "totalTravelDistance" => 0,
            "totalTravelDistanceCO2" => 0,
            "totalTravelDistancePerYear" => 0,
            "totalTravelDistancePerYearCO2" => 0,
            "averageTravelDistance" => 0,
            "averageTravelDistanceCO2" => 0,
            "averageTravelDistancePerYear" => 0,
            "averageTravelDistancePerYearCO2" => 0,
            "totalTravelDuration" => 0,
            "totalTravelDurationPerYear" => 0,
            "averageTravelDuration" => 0,
            "averageTravelDurationPerYear" => 0,
            "nbCarpoolersAsDrivers" => 0,
            "nbCarpoolersAsPassengers" => 0,
            "nbCarpoolersAsBoth" => 0,
            "nbCarpoolersTotal" => 0,
            "humanTotalTravelDuration" => "",
            "humanAverageTravelDuration" => "",
            "humanAverageTravelDurationPerYear" => ""
        ];

        $persons = $mass->getPersons();



        // J'indexe le tableau des personnes pour y accéder ensuite en direct
        $personsIndexed = [];
        foreach ($persons as $person) {
            $personsIndexed[$person->getId()] = $person;
        }

        $tabCoords = [];

        $matrix = new MassMatrix();

        foreach ($persons as $person) {
            $tabCoords[] = array(
                "id"=>$person->getPersonalAddress()->getId(),
                "latitude"=>$person->getPersonalAddress()->getLatitude(),
                "longitude"=>$person->getPersonalAddress()->getLongitude(),
                "distance"=>$person->getDistance(),
                "duration"=>$person->getDuration(),
                "address"=>$person->getPersonalAddress()->getHouseNumber()." ".$person->getPersonalAddress()->getStreet()." ".$person->getPersonalAddress()->getPostalCode()." ".$person->getPersonalAddress()->getAddressLocality()
            );
            $computedData["totalTravelDistance"] += $person->getDistance();
            $computedData["totalTravelDuration"] += $person->getDuration();

            // Can this person carpool ? AsDriver or AsPassenger ? Both ?
            $carpoolAsDriver = false;
            $carpoolAsPassenger = false;
            if (count($person->getMatchingsAsDriver())>0) {
                $computedData["nbCarpoolersAsDrivers"]++;
                $carpoolAsDriver = true;
            }
            if (count($person->getMatchingsAsPassenger())>0) {
                $computedData["nbCarpoolersAsPassengers"]++;
                $carpoolAsPassenger = true;
            }
            if ($carpoolAsDriver && $carpoolAsPassenger) {
                $computedData["nbCarpoolersAsBoth"]++;
            }
            if ($carpoolAsDriver || $carpoolAsPassenger) {
                $computedData["nbCarpoolersTotal"]++;
            }

            // Store the original journey to calculate the gains between original and carpool
            if ($mass->getStatus()==Mass::STATUS_MATCHED && $person->getDistance()!==null) {
                // Only if the matching has been done.
                $journey = new MassJourney(
                    $person->getDistance(),
                    $person->getDuration(),
                    $this->geoTools->getCO2($person->getDistance()),
                    $person->getId()
                );
                $matrix->addOriginalsJourneys($journey);
            }
        }

        $mass->setPersonsCoords($tabCoords);

        // Workingplace storage
//        $mass->setLatWorkingPlace($persons[0]->getWorkAddress()->getLatitude());
//        $mass->setLonWorkingPlace($persons[0]->getWorkAddress()->getLongitude());

        $mass->setWorkingPlaces($this->massPersonRepository->findAllDestinationsForMass($mass));

        // Averages
        $computedData["averageTravelDistance"] = $computedData["totalTravelDistance"] / count($persons);
        $computedData["averageTravelDistancePerYear"] = $computedData["averageTravelDistance"] * Mass::NB_WORKING_DAY;
        $computedData["totalTravelDistancePerYear"] = $computedData["totalTravelDistance"] * Mass::NB_WORKING_DAY;
        $computedData["averageTravelDuration"] = $computedData["totalTravelDuration"] / count($persons);
        $computedData["averageTravelDurationPerYear"] = $computedData["averageTravelDuration"] * Mass::NB_WORKING_DAY;
        $computedData["totalTravelDurationPerYear"] = $computedData["totalTravelDuration"] * Mass::NB_WORKING_DAY;

        // Conversion of some data to human readable versions (like durations in hours, minutes, seconds)
        $computedData["humanTotalTravelDuration"] = $this->formatDataManager->convertSecondsToHuman($computedData["totalTravelDuration"]);
        $computedData["humanTotalTravelDurationPerYear"] = $this->formatDataManager->convertSecondsToHuman($computedData["totalTravelDurationPerYear"]);
        $computedData["humanAverageTravelDuration"] = $this->formatDataManager->convertSecondsToHuman($computedData["averageTravelDuration"]);
        $computedData["humanAverageTravelDurationPerYear"] = $this->formatDataManager->convertSecondsToHuman($computedData["averageTravelDurationPerYear"]);

        // CO2 consumption
        $computedData["averageTravelDistanceCO2"] = $this->geoTools->getCO2($computedData["averageTravelDistance"]);
        $computedData["averageTravelDistancePerYearCO2"] = $this->geoTools->getCO2($computedData["averageTravelDistancePerYear"]);
        $computedData["totalTravelDistanceCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistance"]);
        $computedData["totalTravelDistancePerYearCO2"] = $this->geoTools->getCO2($computedData["totalTravelDistancePerYear"]);

        $mass->setComputedData($computedData);



        // Build the carpooler matrix
        if ($mass->getStatus()==Mass::STATUS_MATCHED) {
            // Only if the matching has been done.
            $matrix = $this->buildCarpoolersMatrix($persons, $matrix, $personsIndexed);

            // Compute the gains between original total and carpool total
            $totalDurationCarpools = 0;
            $totalDistanceCarpools = 0;
            $totalCO2Carpools = 0;
            foreach ($matrix->getCarpools() as $currentCarpool) {
                $totalDistanceCarpools += $currentCarpool->getJourney()->getDistance();
                $totalDurationCarpools += $currentCarpool->getJourney()->getDuration();
                $totalCO2Carpools += $currentCarpool->getJourney()->getCO2();
            }
            $matrix->setSavedDistance($computedData["totalTravelDistance"] - $totalDistanceCarpools);
            $matrix->setSavedDuration($computedData["totalTravelDuration"] - $totalDurationCarpools);
            $matrix->setHumanReadableSavedDuration($this->formatDataManager->convertSecondsToHuman($computedData["totalTravelDuration"] - $totalDurationCarpools));
            $matrix->setSavedCO2($computedData["totalTravelDistanceCO2"] - $totalCO2Carpools);

            $mass->setMassMatrix($matrix);
        }

        return $mass;
    }

    /**
     * Build the carpoolers matrix
     * @param array $persons
     * @param MassMatrix $matrix
     * @param array $personsIndexed
     * @return MassMatrix
     */
    private function buildCarpoolersMatrix(array $persons, MassMatrix $matrix, array $personsIndexed)
    {
        foreach ($persons as $person) {
            $matchingsAsDriver = $person->getMatchingsAsDriver();
            $matchingsAsPassenger = $person->getMatchingsAsPassenger();
            $matrix = $this->linkCarpoolers(array_merge($matchingsAsDriver, $matchingsAsPassenger), $matrix, $personsIndexed);
        }

        return $matrix;
    }

    /**
     * Link carpoolers by keeping the fastest match for the current MassMatching
     * @param array $matchings
     * @param MassMatrix $matrix
     * @param array $personsIndexed
     * @return MassMatrix
     */
    private function linkCarpoolers(array $matchings, MassMatrix $matrix, array $personsIndexed)
    {
        if (count($matchings)>0) {
            $fastestMassPerson1Id = null;
            $fastestMassPerson2Id = null;
            $fastestDistance = 0;
            $fastestDuration = 0;
            $fastestCO2 = 0;
            $biggestGain = -1;
            foreach ($matchings as $matching) {
                $journeyPerson1 = $matrix->getJourneyOfAPerson($matching->getMassPerson1Id());
                $journeyPerson2 = $matrix->getJourneyOfAPerson($matching->getMassPerson2Id());

                // This is the duration if the two peoples drive separately
                $durationJourneySeparately = $journeyPerson1->getDuration() + $journeyPerson2->getDuration();

                // This is the gain between the two peoples driving separately and their carpool
                $gainDurationJourneyCarpool = $durationJourneySeparately-$matching->getDuration();

                // We keep the biggest gain

                if ($gainDurationJourneyCarpool > $biggestGain) {
                    $biggestGain = $gainDurationJourneyCarpool;
                    $fastestDuration = $matching->getDuration();
                    $fastestDistance = $matching->getDistance();
                    $fastestCO2 = $this->geoTools->getCO2($matching->getDistance());
                    $fastestMassPerson1Id = $matching->getMassPerson1Id();
                    $fastestMassPerson2Id = $matching->getMassPerson2Id();
                }
            }

            // As soon as they are linked, we ignore them both. We do not know if it's the best match of all the MassMatchings but it's good enough
            if (count($matrix->getCarpoolsOfAPerson($fastestMassPerson1Id))==0 && count($matrix->getCarpoolsofAPerson($fastestMassPerson2Id))==0) {
                $person1 = $personsIndexed[$fastestMassPerson1Id];
                $person2 = $personsIndexed[$fastestMassPerson2Id];

                $matrix->addCarpools(new MassCarpool(
                    $person1,
                    $person2,
                    new MassJourney($fastestDistance, $fastestDuration, $fastestCO2)
                ));
            }
        }

        return $matrix;
    }

    /**
     * Return all different working places of a Mass
     * @param Mass $mass
     * @return array
     */
    public function getAllWorkingPlaces(Mass $mass)
    {
        $workingPlaces = $this->massPersonRepository->findAllDestinationsForMass($mass);

        return $workingPlaces;
    }
}
