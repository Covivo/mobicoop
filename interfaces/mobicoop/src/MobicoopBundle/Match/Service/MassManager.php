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

namespace Mobicoop\Bundle\MobicoopBundle\Match\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatching;
use Mobicoop\Bundle\MobicoopBundle\Service\UtilsService;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;

/**
 * Mass management service.
 */
class MassManager
{
    private $dataProvider;
    private $userManager;
    
    /**
     * Constructor.
     * @param DataProvider $dataProvider The data provider that provides the Mass
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Mass::class);
        $this->userManager = $userManager;
    }
    
    /**
     * Get an Mass
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function getMass(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $mass = $response->getValue();
            $this->computeResults($mass);
            return $mass;
        }
        return null;
    }
    
    /**
     * Create an mass
     *
     * @param Mass $mass The mass to create
     *
     * @return Mass|null The mass created or null if error.
     */
    public function createMass(Mass $mass)
    {
        $response = $this->dataProvider->postMultiPart($mass);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Delete an mass
     *
     * @param int $id The id of the mass to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteMass(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }

    /**
     * Compute all necessary calculations for a mass
     *
     * @param Mass $mass
     * @return null
     */
    private function computeResults(Mass $mass)
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

        $tabCoords = [];
        $matrix = [
            "originalsJourneys" => [],
            "link" => [],
            "data" => [],
            "saved" => [
                "distance" => 0,
                "duration" => 0,
                "co2" => 0
            ]
        ];

        foreach ($persons as $person) {
            $tabCoords[] = array(
                "latitude"=>$person->getPersonalAddress()->getLatitude(),
                "longitude"=>$person->getPersonalAddress()->getLongitude(),
                "distance"=>$person->getDirection()->getDistance(),
                "duration"=>$person->getDirection()->getDuration()
            );
            $computedData["totalTravelDistance"] += $person->getDirection()->getDistance();
            $computedData["totalTravelDuration"] += $person->getDirection()->getDuration();

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
            $matrix["originalsJourneys"][$person->getId()]["distance"] = $person->getDirection()->getDistance();
            $matrix["originalsJourneys"][$person->getId()]["duration"] = $person->getDirection()->getDuration();
            $matrix["originalsJourneys"][$person->getId()]["co2"] = UtilsService::computeCO2($person->getDirection()->getDistance());
        }

        $mass->setPersonsCoords($tabCoords);

        // Workingplace storage
        $mass->setLatWorkingPlace($persons[0]->getWorkAddress()->getLatitude());
        $mass->setLonWorkingPlace($persons[0]->getWorkAddress()->getLongitude());

        // Averages
        $computedData["averageTravelDistance"] = $computedData["totalTravelDistance"] / count($persons);
        $computedData["averageTravelDistancePerYear"] = $computedData["averageTravelDistance"] * Mass::NB_WORKING_DAY;
        $computedData["totalTravelDistancePerYear"] = $computedData["totalTravelDistance"] * Mass::NB_WORKING_DAY;
        $computedData["averageTravelDuration"] = $computedData["totalTravelDuration"] / count($persons);
        $computedData["averageTravelDurationPerYear"] = $computedData["averageTravelDuration"] * Mass::NB_WORKING_DAY;
        $computedData["totalTravelDurationPerYear"] = $computedData["totalTravelDuration"] * Mass::NB_WORKING_DAY;

        // Conversion of some data to human readable versions (like durations in hours, minutes, seconds)
        $computedData["humanTotalTravelDuration"] = UtilsService::convertSecondsToHumain($computedData["totalTravelDuration"]);
        $computedData["humanTotalTravelDurationPerYear"] = UtilsService::convertSecondsToHumain($computedData["totalTravelDurationPerYear"]);
        $computedData["humanAverageTravelDuration"] = UtilsService::convertSecondsToHumain($computedData["averageTravelDuration"]);
        $computedData["humanAverageTravelDurationPerYear"] = UtilsService::convertSecondsToHumain($computedData["averageTravelDurationPerYear"]);

        // CO2 consumption
        $computedData["averageTravelDistanceCO2"] = UtilsService::computeCO2($computedData["averageTravelDistance"]);
        $computedData["averageTravelDistancePerYearCO2"] = UtilsService::computeCO2($computedData["averageTravelDistancePerYear"]);
        $computedData["totalTravelDistanceCO2"] = UtilsService::computeCO2($computedData["totalTravelDistance"]);
        $computedData["totalTravelDistancePerYearCO2"] = UtilsService::computeCO2($computedData["totalTravelDistancePerYear"]);

        $mass->setComputedData($computedData);



        // Build the carpooler matrix
        $matrix = $this->buildCarpoolersMatrix($persons, $matrix);

        // Compute the gains between original total and carpool total
        $totalDurationCarpools = 0;
        $totalDistanceCarpools = 0;
        $totalCO2Carpools = 0;
        foreach ($matrix["data"] as $currentData){
            $totalDistanceCarpools += $currentData["distance"];
            $totalDurationCarpools += $currentData["duration"];
            $totalCO2Carpools += $currentData["co2"];
        }
        dump($totalDistanceCarpools);
        $matrix["saved"]['distance'] = $computedData["totalTravelDistance"] - $totalDistanceCarpools;
        $matrix["saved"]['duration'] = $computedData["totalTravelDuration"] - $totalDurationCarpools;
        $matrix["saved"]['co2'] = $computedData["totalTravelDistanceCO2"] - $totalCO2Carpools;

        dump($matrix);

        return null;
    }

    /**
     * Build the carpoolers matrix
     * @param ArrayCollection $persons
     * @param array $matrix
     * @return array
     */
    private function buildCarpoolersMatrix(ArrayCollection $persons, array $matrix)
    {
        foreach ($persons as $person) {
            $matchingsAsDriver = $person->getMatchingsAsDriver();
            $matchingsAsPassenger = $person->getMatchingsAsPassenger();
            $matrix = $this->linkCarpoolers(array_merge($matchingsAsDriver, $matchingsAsPassenger), $matrix);
        }

        return $matrix;
    }

    /**
     * Link carpoolers by keeping the fastest match for the current MassMatching
     * @param array $matchings
     * @param array $matrix
     * @return array
     */
    private function linkCarpoolers(array $matchings, array $matrix)
    {
        if (count($matchings)>0) {
            $fastestMassPerson1Id = null;
            $fastestMassPerson2Id = null;
            $fastestDistance = 0;
            $fastestDuration = 0;
            $fastestCO2 = 0;
            $biggestGain = -1;
            foreach ($matchings as $matching) {
                $durationJourneyPerson1 = $matrix["originalsJourneys"][$matching->getMassPerson1Id()]["duration"];
                $durationJourneyPerson2 = $matrix["originalsJourneys"][$matching->getMassPerson2Id()]["duration"];

                // This is the duration if the two peoples drive separately
                $durationJourneySeparately = $durationJourneyPerson1 + $durationJourneyPerson2;

                // This is the gain between the two peoples driving separately and their carpool
                $gainDurationJourneyCarpool = $durationJourneySeparately-$matching->getDirection()->getDuration();

                // We keep the biggest gain

                if ($gainDurationJourneyCarpool > $biggestGain) {
                    $biggestGain = $gainDurationJourneyCarpool;
                    $fastestMassPerson1Id = $matching->getMassPerson1Id();
                    $fastestMassPerson2Id = $matching->getMassPerson2Id();
                    $fastestDuration = $matching->getDirection()->getDuration();
                    $fastestDistance = $matching->getDirection()->getDistance();
                    $fastestCO2 = UtilsService::computeCO2($matching->getDirection()->getDistance());
                }
            }
            // As soon as they are linked, we ignore them both. We do not know if it's the best match of all the MassMatchings but it's good enough
            if (!isset($matrix['link'][$fastestMassPerson1Id]) && !in_array($fastestMassPerson1Id, $matrix['link']) &&
                !isset($matrix['link'][$fastestMassPerson2Id]) && !in_array($fastestMassPerson2Id, $matrix['link'])
            ) {
                $matrix['link'][$fastestMassPerson1Id] = $fastestMassPerson2Id;
                $matrix['data'][$fastestMassPerson1Id]['distance'] = $fastestDistance;
                $matrix['data'][$fastestMassPerson1Id]['duration'] = $fastestDuration;
                $matrix['data'][$fastestMassPerson1Id]['co2'] = $fastestCO2;
            }
        }

        return $matrix;
    }
}
