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
    private $logger;

    public function __construct(
        FormatDataManager $formatDataManager,
        GeoTools $geoTools,
        MassPersonRepository $massPersonRepository,
        LoggerInterface $logger,
        bool $roundTripCompute,
        int $aberrantCoefficient,
        float $kilometerPrice
    ) {
        $this->formatDataManager = $formatDataManager;
        $this->geoTools = $geoTools;
        $this->massPersonRepository = $massPersonRepository;
        $this->roundTripCompute = $roundTripCompute;
        $this->aberrantCoefficient = $aberrantCoefficient;
        $this->kilometerPrice = $kilometerPrice;
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

        $this->logger->info('Mass Compute | Start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $computedData = [
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

        $persons = $mass->getPersons();

        // J'indexe le tableau des personnes pour y accéder ensuite en direct
        $personsIndexed = [];
        foreach ($persons as $person) {
            $personsIndexed[$person->getId()] = $person;
        }

        $this->logger->info('Mass Compute | Index finished for '.count($personsIndexed).' persons | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $tabCoords = [];

        $matrix = new MassMatrix();

        $this->logger->info('Mass Compute | Init matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        foreach ($persons as $person) {
            $this->logger->info('Mass Compute | Init Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $tabCoords[] = [
                'id' => $person->getPersonalAddress()->getId(),
                'latitude' => $person->getPersonalAddress()->getLatitude(),
                'longitude' => $person->getPersonalAddress()->getLongitude(),
                'distance' => $person->getDistance(),
                'duration' => $person->getDuration(),
                'address' => $person->getPersonalAddress()->getHouseNumber().' '.$person->getPersonalAddress()->getStreet().' '.$person->getPersonalAddress()->getPostalCode().' '.$person->getPersonalAddress()->getAddressLocality(),
            ];
            $computedData['totalTravelDistance'] += $person->getDistance();
            $computedData['totalTravelDuration'] += $person->getDuration();

            // Can this person carpool ? AsDriver or AsPassenger ? Both ?
            $carpoolAsDriver = false;
            $carpoolAsPassenger = false;
            if (count($person->getMatchingsAsDriver()) > 0) {
                ++$computedData['nbCarpoolersAsDrivers'];
                $carpoolAsDriver = true;
            }
            if (count($person->getMatchingsAsPassenger()) > 0) {
                ++$computedData['nbCarpoolersAsPassengers'];
                $carpoolAsPassenger = true;
            }
            if ($carpoolAsDriver && $carpoolAsPassenger) {
                ++$computedData['nbCarpoolersAsBoth'];
            }
            if ($carpoolAsDriver || $carpoolAsPassenger) {
                ++$computedData['nbCarpoolersTotal'];
            }

            // Store the original journey to calculate the gains between original and carpool
            if (Mass::STATUS_MATCHED == $mass->getStatus() && null !== $person->getDistance()) {
                // Only if the matching has been done.
                $journey = new MassJourney(
                    $person->getDistance(),
                    $person->getDuration(),
                    $this->geoTools->getCO2($person->getDistance()),
                    $person->getId()
                );
                $matrix->addOriginalsJourneys($journey);
            }
            $this->logger->info('Mass Compute | End Init Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        $this->logger->info('Mass Compute | End Init Matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $mass->setPersonsCoords($tabCoords);

        // Workingplace storage
//        $mass->setLatWorkingPlace($persons[0]->getWorkAddress()->getLatitude());
//        $mass->setLonWorkingPlace($persons[0]->getWorkAddress()->getLongitude());

        $mass->setWorkingPlaces($this->massPersonRepository->findAllDestinationsForMass($mass));

        // Averages
        $computedData['averageTravelDistance'] = $computedData['totalTravelDistance'] / count($persons);
        $computedData['averageTravelDistancePerYear'] = $computedData['averageTravelDistance'] * Mass::NB_WORKING_DAY;
        $computedData['totalTravelDistancePerYear'] = $computedData['totalTravelDistance'] * Mass::NB_WORKING_DAY;
        $computedData['averageTravelDuration'] = $computedData['totalTravelDuration'] / count($persons);
        $computedData['averageTravelDurationPerYear'] = $computedData['averageTravelDuration'] * Mass::NB_WORKING_DAY;
        $computedData['totalTravelDurationPerYear'] = $computedData['totalTravelDuration'] * Mass::NB_WORKING_DAY;

        // Conversion of some data to human readable versions (like durations in hours, minutes, seconds)
        $computedData['humanTotalTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($computedData['totalTravelDuration']);
        $computedData['humanTotalTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($computedData['totalTravelDurationPerYear']);
        $computedData['humanAverageTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($computedData['averageTravelDuration']);
        $computedData['humanAverageTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($computedData['averageTravelDurationPerYear']);

        // CO2 consumption
        $computedData['averageTravelDistanceCO2'] = $this->geoTools->getCO2($computedData['averageTravelDistance']);
        $computedData['averageTravelDistancePerYearCO2'] = $this->geoTools->getCO2($computedData['averageTravelDistancePerYear']);
        $computedData['totalTravelDistanceCO2'] = $this->geoTools->getCO2($computedData['totalTravelDistance']);
        $computedData['totalTravelDistancePerYearCO2'] = $this->geoTools->getCO2($computedData['totalTravelDistancePerYear']);

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

            foreach ($computedData as $key => $data) {
                if (is_numeric($data) && !in_array($key, $coloredList)) {
                    $computedData[$key] = $data * 2;
                }
            }

            // We have to redefined the human version of several computed data
            $computedData['humanTotalTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($computedData['totalTravelDuration']);
            $computedData['humanTotalTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($computedData['totalTravelDurationPerYear']);
            $computedData['humanAverageTravelDuration'] = $this->formatDataManager->convertSecondsToHuman($computedData['averageTravelDuration']);
            $computedData['humanAverageTravelDurationPerYear'] = $this->formatDataManager->convertSecondsToHuman($computedData['averageTravelDurationPerYear']);

            $computedData['roundtripComputed'] = true;
        } else {
            $computedData['roundtripComputed'] = false;
        }

        $mass->setComputedData($computedData);

        // Build the carpooler matrix
        if (Mass::STATUS_MATCHED == $mass->getStatus()) {
            $this->logger->info('Mass Compute | Start Building Matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            // Only if the matching has been done.
            $matrix = $this->buildCarpoolersMatrix($persons, $matrix, $personsIndexed);

            $this->logger->info('Mass Compute | End Building Matrix | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            // Compute the gains between original total and carpool total
            $totalDurationCarpools = 0;
            $totalDistanceCarpools = 0;
            $totalCO2Carpools = 0;
            foreach ($matrix->getCarpools() as $currentCarpool) {
                $totalDistanceCarpools += $currentCarpool->getJourney()->getDistance();
                $totalDurationCarpools += $currentCarpool->getJourney()->getDuration();
                $totalCO2Carpools += $currentCarpool->getJourney()->getCO2();
            }
            $matrix->setSavedDistance($computedData['totalTravelDistance'] - $totalDistanceCarpools);
            $matrix->setSavedMoney(round(($matrix->getSavedDistance() / 1000) * $this->kilometerPrice));
            $matrix->setSavedDuration($computedData['totalTravelDuration'] - $totalDurationCarpools);
            $matrix->setHumanReadableSavedDuration($this->formatDataManager->convertSecondsToHuman($computedData['totalTravelDuration'] - $totalDurationCarpools));
            $matrix->setSavedCO2($computedData['totalTravelDistanceCO2'] - $totalCO2Carpools);

            $mass->setMassMatrix($matrix);
        }

        // check for aberrant addresses
        $mass->setAberrantAddresses($this->checkAberrantAddresses($persons));

        return $mass;
    }

    /**
     * Return all different working places of a Mass.
     *
     * @return array
     */
    public function getAllWorkingPlaces(Mass $mass)
    {
        return $this->massPersonRepository->findAllDestinationsForMass($mass);
    }

    /**
     * Build the carpoolers matrix.
     *
     * @return MassMatrix
     */
    private function buildCarpoolersMatrix(array $persons, MassMatrix $matrix, array $personsIndexed)
    {
        foreach ($persons as $person) {
            $this->logger->info('Mass Compute | Start Building Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $matchingsAsDriver = $person->getMatchingsAsDriver();
            $matchingsAsPassenger = $person->getMatchingsAsPassenger();
            $matrix = $this->linkCarpoolers(array_merge($matchingsAsDriver, $matchingsAsPassenger), $matrix, $personsIndexed);
            $this->logger->info('Mass Compute | End Building Matrix for person '.$person->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        return $matrix;
    }

    /**
     * Link carpoolers by keeping the fastest match for the current MassMatching.
     *
     * @return MassMatrix
     */
    private function linkCarpoolers(array $matchings, MassMatrix $matrix, array $personsIndexed)
    {
        if (count($matchings) > 0) {
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
            if (0 == count($matrix->getCarpoolsOfAPerson($fastestMassPerson1Id)) && 0 == count($matrix->getCarpoolsofAPerson($fastestMassPerson2Id))) {
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

    private function checkAberrantAddresses(array $massPersons): array
    {
        $this->logger->info('Mass Compute | Start Check Aberrant addresses | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $aberrantAddresses = [];

        // Compute the average distance
        $totalDistance = 0;
        foreach ($massPersons as $massPerson) {
            // @var MassPerson $massPerson
            $totalDistance += $massPerson->getDistance();
        }
        $averageDistance = $totalDistance / count($massPersons);

        foreach ($massPersons as $massPerson) {
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
