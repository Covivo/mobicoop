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
 */

namespace App\Match\Service;

use App\Geography\Service\GeoTools;
use App\Match\Entity\Mass;
use App\Match\Entity\MassCarpool;
use App\Match\Entity\MassJourney;
use App\Match\Entity\MassMatrix;
use App\Match\Entity\MassPerson;
use App\Match\Repository\MassPersonRepository;
use App\Service\FormatDataManager;
use Psr\Log\LoggerInterface;

/**
 * Mass compute manager.
 *
 * This service compute all Masses data.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MassComputeManager
{
    private const TIME_LIMIT = 3 * 24 * 60 * 60;

    private $formatDataManager;
    private $geoTools;
    private $massPersonRepository;
    private $roundTripCompute;
    private $aberrantCoefficient;
    private $kilometerPrice;
    private $computeMatrix;
    private $logger;

    private $mass;
    private $computedData;
    private $persons;
    private $massMatrix;
    private $tabCoords;

    public function __construct(
        FormatDataManager $formatDataManager,
        GeoTools $geoTools,
        MassPersonRepository $massPersonRepository,
        LoggerInterface $logger,
        bool $roundTripCompute,
        int $aberrantCoefficient,
        float $kilometerPrice,
        bool $computeMatrix
    ) {
        $this->formatDataManager = $formatDataManager;
        $this->geoTools = $geoTools;
        $this->massPersonRepository = $massPersonRepository;
        $this->roundTripCompute = $roundTripCompute;
        $this->aberrantCoefficient = $aberrantCoefficient;
        $this->kilometerPrice = $kilometerPrice;
        $this->computeMatrix = $computeMatrix;
        $this->logger = $logger;
    }

    /**
     * Compute all necessary calculations for a mass.
     *
     * @return Mass
     */
    public function computeResults(Mass $mass)
    {
        set_time_limit(self::TIME_LIMIT);

        $this->mass = $mass;

        $this->logger->info('Mass Compute | Start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->computedData = [
            'totalTravelDistance' => 0,
            'totalTravelDistanceCO2' => 0,
            'totalTravelDistancePerYear' => 0,
            'totalTravelDistancePerYearCO2' => 0,
            'averageTravelDistance' => 0,
            'averageTravelDistanceCO2' => 0,
            'averageTravelDistancePerYear' => 0,
            'averageTravelDistancePerYearCO2' => 0,
            'totalTravelDuration' => 0,
            'totalTravelDurationPerYear' => 0,
            'averageTravelDuration' => 0,
            'averageTravelDurationPerYear' => 0,
            'nbCarpoolersAsDrivers' => 0,
            'nbCarpoolersAsPassengers' => 0,
            'nbCarpoolersAsBoth' => 0,
            'nbCarpoolersTotal' => 0,
            'humanTotalTravelDuration' => '',
            'humanTotalTravelDurationPerYear' => '',
            'humanAverageTravelDuration' => '',
            'humanAverageTravelDurationPerYear' => '',
            'kilometerPrice' => $this->kilometerPrice,
        ];

        $this->persons = $this->mass->getPersons();

        // J'indexe le tableau des personnes pour y accéder ensuite en direct
        $this->logger->info('Mass Compute | Index persons started '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $personsIndexed = [];
        foreach ($this->persons as $person) {
            $personsIndexed[$person->getId()] = $person;
        }

        $this->logger->info('Mass Compute | Index finished for '.count($personsIndexed).' persons | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $this->tabCoords = [];

        $this->massMatrix = new MassMatrix();

        $this->computeData();

        $this->buildMatrixOriginalsJourneys();

        $this->buildPersonsCoords();

        // Workingplace storage
//        $this->mass->setLatWorkingPlace($this->persons[0]->getWorkAddress()->getLatitude());
//        $this->mass->setLonWorkingPlace($this->persons[0]->getWorkAddress()->getLongitude());

        $this->mass->setWorkingPlaces($this->getAllWorkingPlaces());

        // Averages
        $this->computedData['averageTravelDistance'] = $this->computedData['totalTravelDistance'] / count($this->persons);
        $this->computedData['averageTravelDistancePerYear'] = $this->computedData['averageTravelDistance'] * Mass::NB_WORKING_DAY;
        $this->computedData['totalTravelDistancePerYear'] = $this->computedData['totalTravelDistance'] * Mass::NB_WORKING_DAY;
        $this->computedData['averageTravelDuration'] = $this->computedData['totalTravelDuration'] / count($this->persons);
        $this->computedData['averageTravelDurationPerYear'] = $this->computedData['averageTravelDuration'] * Mass::NB_WORKING_DAY;
        $this->computedData['totalTravelDurationPerYear'] = $this->computedData['totalTravelDuration'] * Mass::NB_WORKING_DAY;

        // Conversion of some data to human readable versions (like durations in hours, minutes, seconds)
        $this->computedData['humanTotalTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['totalTravelDuration']);
        $this->computedData['humanTotalTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['totalTravelDurationPerYear']);
        $this->computedData['humanAverageTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['averageTravelDuration']);
        $this->computedData['humanAverageTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['averageTravelDurationPerYear']);

        // CO2 consumption
        $this->computedData['averageTravelDistanceCO2'] = $this->geoTools->getCO2($this->computedData['averageTravelDistance']);
        $this->computedData['averageTravelDistancePerYearCO2'] = $this->geoTools->getCO2($this->computedData['averageTravelDistancePerYear']);
        $this->computedData['totalTravelDistanceCO2'] = $this->geoTools->getCO2($this->computedData['totalTravelDistance']);
        $this->computedData['totalTravelDistancePerYearCO2'] = $this->geoTools->getCO2($this->computedData['totalTravelDistancePerYear']);

        // If we compute for round trip, we multiply everything by two
        if ($this->roundTripCompute) {
            // Not a blacklist 'cause... you know...
            $coloredList = [
                'nbCarpoolersAsDrivers',
                'nbCarpoolersAsPassengers',
                'nbCarpoolersAsBoth',
                'nbCarpoolersTotal',
                'kilometerPrice',
            ];

            foreach ($this->computedData as $key => $data) {
                if (is_numeric($data) && !in_array($key, $coloredList)) {
                    $this->computedData[$key] = $data * 2;
                }
            }

            // We have to redefined the human version of several computed data
            $this->computedData['humanTotalTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['totalTravelDuration']);
            $this->computedData['humanTotalTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['totalTravelDurationPerYear']);
            $this->computedData['humanAverageTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['averageTravelDuration']);
            $this->computedData['humanAverageTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($this->computedData['averageTravelDurationPerYear']);

            $this->computedData['roundtripComputed'] = true;
        } else {
            $this->computedData['roundtripComputed'] = false;
        }

        $mass->setComputedData($this->computedData);

        // Build the carpooler matrix
        if (Mass::STATUS_MATCHED == $mass->getStatus()) {
            $this->logger->info('Mass Compute | Start Building Matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            // Only if the matching has been done.
            if ($this->computeMatrix) {
                $this->buildCarpoolersMatrix($personsIndexed);
            }

            $this->logger->info('Mass Compute | End Building Matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            // Compute the gains between original total and carpool total
            $totalDurationCarpools = 0;
            $totalDistanceCarpools = 0;
            $totalCO2Carpools = 0;
            foreach ($this->massMatrix->getCarpools() as $currentCarpool) {
                $totalDistanceCarpools += $currentCarpool->getJourney()->getDistance();
                $totalDurationCarpools += $currentCarpool->getJourney()->getDuration();
                $totalCO2Carpools += $currentCarpool->getJourney()->getCO2();
            }
            $this->massMatrix->setSavedDistance($this->computedData['totalTravelDistance'] - $totalDistanceCarpools);
            $this->massMatrix->setSavedMoney(round(($this->massMatrix->getSavedDistance() / 1000) * $this->kilometerPrice));
            $this->massMatrix->setSavedDuration($this->computedData['totalTravelDuration'] - $totalDurationCarpools);
            $this->massMatrix->setHumanReadableSavedDuration($this->formatDataManager->convertSecondsToHuman($this->computedData['totalTravelDuration'] - $totalDurationCarpools));
            $this->massMatrix->setSavedCO2($this->computedData['totalTravelDistanceCO2'] - $totalCO2Carpools);

            $this->mass->setMassMatrix($this->massMatrix);
        }

        // check for aberrant addresses
        $this->mass->setAberrantAddresses($this->checkAberrantAddresses());

        return $this->mass;
    }

    private function buildPersonsCoords()
    {
        $this->logger->info('Mass Compute | Begin buildPersonsCoords | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($this->persons as $person) {
            $this->tabCoords[] = [
                'id' => $person->getPersonalAddress()->getId(),
                'latitude' => $person->getPersonalAddress()->getLatitude(),
                'longitude' => $person->getPersonalAddress()->getLongitude(),
                'distance' => $person->getDistance(),
                'duration' => $person->getDuration(),
                'address' => $person->getPersonalAddress()->getHouseNumber().' '.$person->getPersonalAddress()->getStreet().' '.$person->getPersonalAddress()->getPostalCode().' '.$person->getPersonalAddress()->getAddressLocality(),
            ];
        }
        $this->mass->setPersonsCoords($this->tabCoords);
        $this->logger->info('Mass Compute | End buildPersonsCoords | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }
    
    private function computeData()
    {
        $this->logger->info('Mass Compute | Begin computeData | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($this->persons as $person) {
            $this->computedData['totalTravelDistance'] += $person->getDistance();
            $this->computedData['totalTravelDuration'] += $person->getDuration();

            // Can this person carpool ? AsDriver or AsPassenger ? Both ?
            $carpoolAsDriver = false;
            $carpoolAsPassenger = false;
            if (count($person->getMatchingsAsDriver()) > 0) {
                ++$this->computedData['nbCarpoolersAsDrivers'];
                $carpoolAsDriver = true;
            }
            if (count($person->getMatchingsAsPassenger()) > 0) {
                ++$this->computedData['nbCarpoolersAsPassengers'];
                $carpoolAsPassenger = true;
            }
            if ($carpoolAsDriver && $carpoolAsPassenger) {
                ++$this->computedData['nbCarpoolersAsBoth'];
            }
            if ($carpoolAsDriver || $carpoolAsPassenger) {
                ++$this->computedData['nbCarpoolersTotal'];
            }
        }
        $this->logger->info('Mass Compute | End computeData | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    private function buildMatrixOriginalsJourneys()
    {
        $this->logger->info('Mass Compute | Begin buildMatrixOriginalsJourneys | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($this->persons as $person) {
            // Store the original journey to calculate the gains between original and carpool
            if (Mass::STATUS_MATCHED == $this->mass->getStatus() && null !== $person->getDistance()) {
                // Only if the matching has been done.
                $journey = new MassJourney(
                    $person->getDistance(),
                    $person->getDuration(),
                    $this->geoTools->getCO2($person->getDistance()),
                    $person->getId()
                );
                $this->massMatrix->addOriginalsJourneys($journey);
            }
        }
        $this->logger->info('Mass Compute | Begin buildMatrixOriginalsJourneys | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Return all different working places of a Mass.
     *
     * @return array
     */
    public function getAllWorkingPlaces()
    {
        $this->logger->info('Mass Compute | Begin getAllWorkingPlaces | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        return $this->massPersonRepository->findAllDestinationsForMass($this->mass);
        $this->logger->info('Mass Compute | End getAllWorkingPlaces | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Build the carpoolers matrix.
     *
     * @return MassMatrix
     */
    private function buildCarpoolersMatrix(array $personsIndexed)
    {
        foreach ($this->persons as $person) {
            echo "--------------------\n";
            echo "Person : ".$person->getId()."\n";
            echo "--------------------\n";
            $this->logger->info('Mass Compute | Start Building Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $matchingsAsDriver = $person->getMatchingsAsDriver();
            $matchingsAsPassenger = $person->getMatchingsAsPassenger();
            $this->massMatrix = $this->linkCarpoolers(array_merge($matchingsAsDriver, $matchingsAsPassenger), $personsIndexed);
            $this->logger->info('Mass Compute | End Building Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }
    }

    /**
     * Link carpoolers by keeping the fastest match for the current MassMatching.
     *
     * @return MassMatrix
     */
    private function linkCarpoolers(array $matchings, array $personsIndexed)
    {
        if (count($matchings) > 0) {
            $fastestMassPerson1Id = null;
            $fastestMassPerson2Id = null;
            $fastestDistance = 0;
            $fastestDuration = 0;
            $fastestCO2 = 0;
            $biggestGain = -1;
            foreach ($matchings as $matching) {
                echo "----------Debut matching\n";
                echo "Matching ID : ".$matching->getId()."\n";
                $journeyPerson1 = $this->massMatrix->getJourneyOfAPerson($matching->getMassPerson1Id());
                $journeyPerson2 = $this->massMatrix->getJourneyOfAPerson($matching->getMassPerson2Id());

                // This is the duration if the two peoples drive separately
                $durationJourneySeparately = $journeyPerson1->getDuration() + $journeyPerson2->getDuration();

                // This is the gain between the two peoples driving separately and their carpool
                $gainDurationJourneyCarpool = $durationJourneySeparately - $matching->getDuration();

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
            if (null !== $fastestMassPerson1Id && null !== $fastestMassPerson2Id && 0 == count($this->massMatrix->getCarpoolsOfAPerson($fastestMassPerson1Id)) && 0 == count($this->massMatrix->getCarpoolsofAPerson($fastestMassPerson2Id))) {
                $person1 = $personsIndexed[$fastestMassPerson1Id];
                $person2 = $personsIndexed[$fastestMassPerson2Id];

                $this->massMatrix->addCarpools(new MassCarpool(
                    $person1,
                    $person2,
                    new MassJourney($fastestDistance, $fastestDuration, $fastestCO2)
                ));
            }
        }
    }

    private function checkAberrantAddresses(): array
    {
        $this->logger->info('Mass Compute | Start Check Aberrant addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $aberrantAddresses = [];

        // Compute the average distance
        $totalDistance = 0;
        foreach ($this->persons as $massPerson) {
            // @var MassPerson $massPerson
            $totalDistance += $massPerson->getDistance();
        }
        $averageDistance = $totalDistance / count($this->persons);

        foreach ($this->persons as $massPerson) {
            /**
             * @var MassPerson $massPerson
             */
            if ($massPerson->getDistance() > ($averageDistance * $this->aberrantCoefficient)) {
                $origin = trim(
                    $massPerson->getPersonalAddress()->getHouseNumber().' '.
                    $massPerson->getPersonalAddress()->getStreet().' '.
                    $massPerson->getPersonalAddress()->getPostalCode().' '.
                    $massPerson->getPersonalAddress()->getAddressLocality().' '.
                    $massPerson->getPersonalAddress()->getAddressCountry()
                );
                $destination = trim(
                    $massPerson->getWorkAddress()->getHouseNumber().' '.
                    $massPerson->getWorkAddress()->getStreet().' '.
                    $massPerson->getWorkAddress()->getPostalCode().' '.
                    $massPerson->getWorkAddress()->getAddressLocality().' '.
                    $massPerson->getWorkAddress()->getAddressCountry()
                );

                $aberrantAddresses[] = '<'.$origin.'> => <'.$destination.'>, Distance : '.round($massPerson->getDistance() / 1000, 1).' kms, id #'.$massPerson->getGivenId();
            }
        }

        $this->logger->info('Mass Compute | End Check Aberrant addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        return $aberrantAddresses;
    }
}
