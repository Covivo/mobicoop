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

use App\Geography\Entity\Address;
use App\Geography\Interfaces\GeorouterInterface;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\GeoSearcher;
use App\Geography\Service\GeoTools;
use App\Match\Entity\Candidate;
use App\Match\Entity\Mass;
use App\Match\Entity\MassData;
use App\Match\Entity\MassPerson;
use App\Match\Event\MassAnalyzeErrorsEvent;
use App\Match\Event\MassMatchedEvent;
use App\Match\Exception\MassException;
use App\Match\Repository\MassPersonRepository;
use App\Match\Repository\MassRepository;
use App\Service\FileManager;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Geocoder\Location;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Mass import manager.
 *
 * This service contains methods related to mass matching file import.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class MassImportManager
{
    public const MIMETYPE_ZIP = 'application/zip';
    public const MIMETYPE_APPLICATION_XML = 'application/xml';
    public const MIMETYPE_TEXT_XML = 'text/xml';
    public const MIMETYPE_CSV = 'text/csv';
    public const MIMETYPE_PLAIN = 'text/plain';
    public const MIMETYPE_JSON = 'application/json';

    public const DEFAULT_OUTWARD_TIME = '08:00:00';
    public const DEFAULT_RETURN_TIME = '18:00:00';

    public const TIME_LIMIT = 3 * 24 * 60 * 60;
    private const MEMORY_LIMIT = 4096;

    private const COMMON_ORIGIN_THRESHOLD_PERCENT = 30;

    private const BATCH_GEOCODER = 50;
    private const BATCH_GEOROUTER = 10;

    private $entityManager;
    private $massRepository;
    private $massPersonRepository;
    private $fileManager;
    private $logger;
    private $params;
    private $validator;
    private $geoTools;
    private $geoSearcher;
    private $geoRouter;
    private $geoMatcher;
    private $eventDispatcher;
    private $geocoder;

    /**
     * Constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        MassRepository $massRepository,
        MassPersonRepository $massPersonRepository,
        FileManager $fileManager,
        LoggerInterface $logger,
        ValidatorInterface $validator,
        GeoTools $geoTools,
        GeoSearcher $geoSearcher,
        PluginProvider $geocoder,
        GeoRouter $geoRouter,
        GeoMatcher $geoMatcher,
        EventDispatcherInterface $eventDispatcher,
        array $params
    ) {
        $this->entityManager = $entityManager;
        $this->massRepository = $massRepository;
        $this->massPersonRepository = $massPersonRepository;
        $this->fileManager = $fileManager;
        $this->logger = $logger;
        $this->params = $params;
        $this->validator = $validator;
        $this->geoTools = $geoTools;
        $this->geoSearcher = $geoSearcher;
        $this->geoRouter = $geoRouter;
        $this->geoMatcher = $geoMatcher;
        $this->eventDispatcher = $eventDispatcher;
        $this->geocoder = $geocoder;
    }

    /**
     * Get a mass by its id.
     *
     * @param int $id the id of the mass
     *
     * @return null|Mass The mass found or null if not found
     */
    public function getMass(int $id)
    {
        return $this->massRepository->find($id);
    }

    /**
     * Create a mass.
     *
     * @param Mass $mass The mass to create
     *
     * @return Mass The mass created
     */
    public function createMass(Mass $mass)
    {
        set_time_limit(self::TIME_LIMIT);

        // we associate the user and the mass
        $mass->getUser()->addMass($mass);
        // we rename the file
        $mass->setFileName($this->generateFilename($mass));

        $mass->setStatus(Mass::STATUS_INCOMING);

        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        // the file is uploaded, we treat it and return it
        $mass = $this->treatMass($mass);

        // if we have an error we remove the file
        if (count($mass->getErrors()) > 0) {
            $mass->setStatus(Mass::STATUS_INVALID);
            $originalMass = clone $mass;
            $this->entityManager->remove($mass);
            $this->entityManager->flush();

            return $originalMass;
        }

        $mass->setStatus(Mass::STATUS_VALID);
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        return $mass;
    }

    /**
     * @param Mass $mass   The mass to update
     * @param int  $status The final status
     */
    public function updateStatusMass(Mass $mass, int $status)
    {
        $mass->setStatus($status);
        $this->entityManager->persist($mass);
        $this->entityManager->flush();
    }

    /**
     * Analyze mass file data.
     *
     * @param Mass $mass The mass to analyze
     */
    public function analyzeMass(Mass $mass)
    {
        set_time_limit(self::TIME_LIMIT);
        ini_set('memory_limit', self::MEMORY_LIMIT.'M');
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->logger->info('Mass analyze | Start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $mass->setStatus(Mass::STATUS_ANALYZING);
        $mass->setAnalyzingDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        // we create an array to keep all the analysing errors
        $analyseErrors = [];

        // we search all destinations
        $this->logger->info('Mass analyze | Find all destinations start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $destinations = $this->massPersonRepository->findAllDestinationsForMass($mass);
        $this->logger->info('Mass analyze | Find all destinations end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // we geocode the destinations
        $geocodedDestinations = [];
        $this->logger->info('Mass analyze | Geocode destinations start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        foreach ($destinations as $key => $destination) {
            $input = trim(str_replace(
                ',',
                '',
                trim($destination['houseNumber']).' '.trim($destination['street']).' '.trim($destination['postalCode']).' '.trim($destination['addressLocality'])
            ));
            if (!$address = $this->geoCode($input)) {
                $analyseErrors[] = 'Destination address <'.$input.'> not found';
                $this->logger->info('Mass analyze | Destination address <'.$input.'> not found | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

                continue;
            }

            $geocodedDestinations[$key] = $address;

            $this->logger->info('Mass analyze | Geocoded destination | '.$key.' | '.$address->getProvidedBy().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }
        $this->logger->info('Mass analyze | Geocode destinations end, '.count($destinations).' destinations found | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        if (0 == count($analyseErrors)) {
            // origins are usually different
            // we first check if there are many common origins
            $this->logger->info('Mass analyze | Find all origins start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $origins = $this->massPersonRepository->findAllOriginsForMass($mass);
            $this->logger->info('Mass analyze | Find all origins end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            $geocodedOrigins = false;
            if ((count($origins) * 100 / count($mass->getPersons())) >= self::COMMON_ORIGIN_THRESHOLD_PERCENT) {
                // we geocode the origins
                $geocodedOrigins = [];
                $this->logger->info('Mass analyze | Geocode origins start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                foreach ($origins as $key => $origin) {
                    $input = trim(str_replace(
                        ',',
                        '',
                        trim($origin['houseNumber']).' '.trim($origin['street']).' '.trim($origin['postalCode']).' '.trim($origin['addressLocality'])
                    ));
                    if (!$address = $this->geoCode($input)) {
                        $analyseErrors[] = 'Origin address <'.$input.'> not found';
                        $this->logger->info('Mass analyze | Origin address <'.$input.'> not found | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

                        continue;
                    }

                    $geocodedOrigins[$key] = $address;

                    $this->logger->info('Mass analyze | Geocoded origin | '.$key.' | '.$address->getProvidedBy().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                }
                $this->logger->info('Mass analyze | Geocode origins end, '.count($origins).' origins found | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            }

            gc_enable();
            $this->print_mem(1);
            $batch = 0;
            foreach ($mass->getPersons() as $massPerson) {
                if (is_array($geocodedOrigins)) {
                    // we search the origin (already calculated in the previous step)
                    foreach ($origins as $key => $origin) {
                        if (
                            $origin['houseNumber'] == $massPerson->getPersonalAddress()->getHouseNumber()
                            && $origin['street'] == $massPerson->getPersonalAddress()->getStreet()
                            && $origin['postalCode'] == $massPerson->getPersonalAddress()->getPostalCode()
                            && $origin['addressLocality'] == $massPerson->getPersonalAddress()->getAddressLocality()
                            && $origin['addressCountry'] == $massPerson->getPersonalAddress()->getAddressCountry()
                        ) {
                            $massPerson->getPersonalAddress()->setLongitude($geocodedOrigins[$key]->getLongitude());
                            $massPerson->getPersonalAddress()->setLatitude($geocodedOrigins[$key]->getLatitude());
                            $this->logger->info('Mass analyze | Found personal address '.$key.' for person '.$massPerson->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

                            break;
                        }
                    }
                } else {
                    // maybe we already have the gps points
                    if (
                        !is_null($massPerson->getPersonalAddress()->getLongitude())
                        && !is_null($massPerson->getPersonalAddress()->getLongitude())
                        && !is_null($massPerson->getWorkAddress()->getLongitude())
                        && !is_null($massPerson->getWorkAddress()->getLongitude())
                    ) {
                        continue;
                    }
                    // no gps points
                    $input = trim($massPerson->getPersonalAddress()->getHouseNumber()).' '.
                    trim($massPerson->getPersonalAddress()->getStreet()).' '.
                    trim($massPerson->getPersonalAddress()->getPostalCode()).' '.
                    trim($massPerson->getPersonalAddress()->getAddressLocality()).' '.
                    trim($massPerson->getPersonalAddress()->getAddressCountry());
                    $this->logger->info('Mass analyze | Geocode personal address for person '.$massPerson->getGivenId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                    // strip the coma
                    $input = str_replace(',', '', $input);

                    if ($address = $this->geoCode($input)) {
                        $massPerson->getPersonalAddress()->setLongitude($address->getLongitude());
                        $massPerson->getPersonalAddress()->setLatitude($address->getLatitude());
                        $this->logger->info('Mass analyze | Geocoded personal address for person '.$massPerson->getGivenId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                        $address = null;
                        unset($address);
                        $input = null;
                        unset($input);
                    } else {
                        $analyseErrors[] = 'Personal address <'.$input.'> not found for id #'.$massPerson->getGivenId();
                    }
                }
                // we search the destination (already calculated in the previous step)
                foreach ($destinations as $key => $destination) {
                    if (
                        $destination['houseNumber'] == $massPerson->getWorkAddress()->getHouseNumber()
                        && $destination['street'] == $massPerson->getWorkAddress()->getStreet()
                        && $destination['postalCode'] == $massPerson->getWorkAddress()->getPostalCode()
                        && $destination['addressLocality'] == $massPerson->getWorkAddress()->getAddressLocality()
                        && $destination['addressCountry'] == $massPerson->getWorkAddress()->getAddressCountry()
                    ) {
                        $massPerson->getWorkAddress()->setLongitude($geocodedDestinations[$key]->getLongitude());
                        $massPerson->getWorkAddress()->setLatitude($geocodedDestinations[$key]->getLatitude());
                        $this->logger->info('Mass analyze | Found work address '.$key.' for person '.$massPerson->getId().' | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

                        break;
                    }
                }
                $this->entityManager->persist($massPerson);
                ++$batch;
                if (self::BATCH_GEOCODER == $batch) {
                    $this->entityManager->flush();
                    $batch = 0;
                    gc_collect_cycles();
                    $this->print_mem(2);
                }
            }
            $this->entityManager->flush();
            $this->logger->info('Mass analyze | Geocode personal address end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            gc_collect_cycles();
            $this->print_mem(3);

            // all addresses are geocoded, we can get the directions
            $this->logger->info('Mass analyze | Direction personal address start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

            $i = 0;
            foreach ($mass->getPersons() as $massPerson) {
                $addressesForRoutes[$i] = [
                    [
                        0 => $massPerson->getPersonalAddress(),
                        1 => $massPerson->getWorkAddress(),
                    ],
                ];
                $routesOwner[$i] = $massPerson;
                ++$i;
            }
            $ownerRoutes = $this->geoRouter->getMultipleAsyncRoutes($addressesForRoutes, false, false, GeorouterInterface::RETURN_TYPE_ARRAY);

            $i = 1;
            $batch = 0;
            foreach ($routesOwner as $key => $massPerson) {
                if (isset($ownerRoutes[$key])) {
                    $route = $ownerRoutes[$key][0];
                    $massPerson->setDistance($route['distance']);
                    $massPerson->setDuration($route['duration']);
                    $massPerson->setBboxMinLon($route['bbox'][0]);
                    $massPerson->setBboxMinLat($route['bbox'][1]);
                    $massPerson->setBboxMaxLon($route['bbox'][2]);
                    $massPerson->setBboxMaxLat($route['bbox'][3]);
                    $massPerson->setBearing($route['bearing']);
                    $this->entityManager->persist($massPerson);
                    ++$batch;
                    if (self::BATCH_GEOCODER == $batch) {
                        $this->entityManager->flush();
                        $batch = 0;
                        $this->print_mem(4);
                    }
                } else {
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
                    $analyseErrors[] = 'No route found for <'.$origin.'> => <'.$destination.'>, id #'.$massPerson->getGivenId();
                }
                $this->logger->info('Mass analyze | Direction personal address n°'.$i.' end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                ++$i;
            }

            $this->logger->info('Mass analyze | Direction personal address end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        }

        // Handling errors
        $mass->setErrors($analyseErrors);
        if (count($analyseErrors) > 0) {
            // Some errors have been found. We send an email to the operator
            $event = new MassAnalyzeErrorsEvent($mass);
            $this->eventDispatcher->dispatch(MassAnalyzeErrorsEvent::NAME, $event);
        }

        $mass->setStatus(Mass::STATUS_ANALYZED);
        $mass->setAnalyzedDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();
        $this->logger->info('Mass analyze | End '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    /**
     * Match mass file data.
     *
     * @param Mass  $mass                     The mass to match
     * @param int   $maxDetourDurationPercent The maximum detour duration in percent of the original duration
     * @param int   $maxDetourDistancePercent The maximum detour distance in percent of the original distance
     * @param float $minOverlapRatio          The minimum overlap ratio between bouding boxes to try a match
     * @param float $maxSuperiorDistanceRatio The maximum superior distance ratio between A and B to try a match
     * @param bool  $bearingCheck             Check the bearings
     * @param int   $bearingRange             The bearing range if check bearings
     */
    public function matchMass(
        Mass $mass,
        int $maxDetourDurationPercent = 40,
        int $maxDetourDistancePercent = 40,
        float $minOverlapRatio = 0,
        float $maxSuperiorDistanceRatio = 1000,
        bool $bearingCheck = true,
        int $bearingRange = 10
    ) {
        set_time_limit(self::TIME_LIMIT);
        ini_set('memory_limit', self::MEMORY_LIMIT.'M');
        //$this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        //gc_enable();
        $this->print_mem(1);

        $this->logger->info('Mass match | Start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        $mass->setStatus(Mass::STATUS_MATCHING);
        $mass->setCalculationDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        $batch = 0;
        $candidates = [];
        $insertValues = [];

        // we search the matches for all the persons
        foreach ($mass->getPersons() as $driverPerson) {
            $this->logger->info('Mass match | Searching candidates for person n°'.$driverPerson->getId().' start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->print_mem(2);
            // if the person is not driver we skip this person
            if (!$driverPerson->isDriver()) {
                continue;
            }
            // if the person has no geography information we skip this person (meaning that its information is incorrect or has not been found by the SIG)
            if (
                is_null($driverPerson->getDistance()) || (0 == $driverPerson->getDistance())
                || is_null($driverPerson->getDuration()) || (0 == $driverPerson->getDuration())
                || is_null($driverPerson->getBboxMinLon())
                || is_null($driverPerson->getBboxMinLat())
                || is_null($driverPerson->getBboxMaxLon())
                || is_null($driverPerson->getBboxMaxLat())) {
                continue;
            }
            $candidateDriver = new Candidate();
            $candidateDriver->setAddresses([$driverPerson->getPersonalAddress(), $driverPerson->getWorkAddress()]);
            $candidateDriver->setMaxDetourDurationPercent($maxDetourDurationPercent);
            $candidateDriver->setMaxDetourDistancePercent($maxDetourDistancePercent);
            $candidateDriver->setDistance($driverPerson->getDistance());
            $candidateDriver->setDuration($driverPerson->getDuration());
            $candidateDriver->setId($driverPerson->getId());
            $candidateDriver->setMassPerson($driverPerson);
            $candidatePassengers = [];
            $bbox_driver = [
                $driverPerson->getBboxMinLon(),
                $driverPerson->getBboxMinLat(),
                $driverPerson->getBboxMaxLon(),
                $driverPerson->getBboxMaxLat(),
                $driverPerson->getId(),
            ];
            $range = [];
            if ($bearingCheck) {
                $range = $this->geoTools->getOppositeBearing($driverPerson->getBearing(), $bearingRange);
            }
            foreach ($mass->getPersons() as $passengerPerson) {
                // we check if the candidateDriver is the current candidate
                if ($driverPerson->getId() == $passengerPerson->getId()) {
                    continue;
                }
                // we check if the candidate can be passenger
                if (!$passengerPerson->isPassenger()) {
                    continue;
                }
                // if the person has no geography information we skip this person (meaning that its information is incorrect or has not been found by the SIG)
                if (
                    is_null($passengerPerson->getDistance()) || (0 == $passengerPerson->getDistance())
                    || is_null($passengerPerson->getDuration()) || (0 == $passengerPerson->getDuration())
                    || is_null($passengerPerson->getBboxMinLon())
                    || is_null($passengerPerson->getBboxMinLat())
                    || is_null($passengerPerson->getBboxMaxLon())
                    || is_null($passengerPerson->getBboxMaxLat())) {
                    continue;
                }
                // we check if bearings are to be checked
                if ($bearingCheck && $range['min'] <= $range['max']) {
                    if ($range['min'] <= $passengerPerson->getBearing() && $range['max'] >= $passengerPerson->getBearing()) {
                        // usual case, eg. 140 to 160
                        continue;
                    }
                } elseif ($bearingCheck && $range['min'] > $range['max']) {
                    if ($range['min'] <= $passengerPerson->getBearing() || $range['max'] >= $passengerPerson->getBearing()) {
                        // the range is like between 350 and 10, we have to check 350->360 and 0->10
                        continue;
                    }
                }
                $bbox_passenger = [
                    $passengerPerson->getBboxMinLon(),
                    $passengerPerson->getBboxMinLat(),
                    $passengerPerson->getBboxMaxLon(),
                    $passengerPerson->getBboxMaxLat(),
                    $passengerPerson->getId(),
                ];
                // we check if the overlap ratio is ok
                $overlap = $this->overlap_ratio($bbox_driver, $bbox_passenger);
                if ($overlap < $minOverlapRatio) {
                    $overlap = null;
                    unset($overlap);
                    $bbox_passenger = null;
                    unset($bbox_passenger);

                    continue;
                }
                // we check if the candidate distance is superior than the candidateDriver distance by the specified ratio
                if (($candidateDriver->getMassPerson()->getDistance() < $passengerPerson->getDistance())
                    && (($passengerPerson->getDistance() / $candidateDriver->getMassPerson()->getDistance()) > $maxSuperiorDistanceRatio)) {
                    $overlap = null;
                    unset($overlap);
                    $bbox_passenger = null;
                    unset($bbox_passenger);

                    continue;
                }
                // here we know the candidate is really a potential candidate !
                $candidatePassenger = new Candidate();
                $candidatePassenger->setAddresses([$passengerPerson->getPersonalAddress(), $passengerPerson->getWorkAddress()]);
                $candidatePassenger->setId($passengerPerson->getId());
                $candidatePassenger->setMassPerson($passengerPerson);
                $candidatePassengers[] = $candidatePassenger;
            }

            $candidates[] = [
                'driver' => $candidateDriver,
                'passengers' => $candidatePassengers,
            ];

            $this->logger->info('Mass match | Searching candidates for person n°'.$driverPerson->getId().' end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->print_mem(3);

            ++$batch;

            if ($batch >= self::BATCH_GEOROUTER) {
                // we try to match with the candidates
                $this->logger->info('Mass match | Creating matches records start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                if ($matches = $this->geoMatcher->multiMatch($candidates, true)) {
                    if (is_array($matches) && count($matches) > 0) {
                        foreach ($matches as $match) {
                            foreach ($match['matches'] as $matched) {
                                $insertValues[] = [
                                    $driverPerson->getId(),
                                    $match['passenger']->getMassPerson()->getId(),
                                    $matched['newDistance'],
                                    $matched['newDuration'],
                                ];
                            }
                        }
                    }
                }
                $candidates = [];
                $batch = 0;
            }

            if (count($insertValues) >= 1000) {
                $insertRequest = 'insert into mass_matching (mass_person1_id, mass_person2_id, distance, duration) values ';
                foreach ($insertValues as $value) {
                    $insertRequest .= '('.implode(',', $value).'),';
                }
                $insertRequest .= rtrim($insertRequest, ',').';';
                $this->logger->info('Mass match | Insert mass matchings | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
                $this->entityManager->getConnection()->prepare($insertRequest)->execute();
                $insertValues = [];
            }
        }
        // we try to match with the candidates a last time
        $this->logger->info('Mass match | Creating matches records start '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        if ($matches = $this->geoMatcher->multiMatch($candidates, true)) {
            if (is_array($matches) && count($matches) > 0) {
                foreach ($matches as $match) {
                    foreach ($match['matches'] as $matched) {
                        $insertValues[] = [
                            $driverPerson->getId(),
                            $match['passenger']->getMassPerson()->getId(),
                            $matched['newDistance'],
                            $matched['newDuration'],
                        ];
                    }
                }
            }
        }
        if (count($insertValues) > 0) {
            $insertRequest = 'insert into mass_matching (mass_person1_id, mass_person2_id, distance, duration) values ';
            foreach ($insertValues as $value) {
                $insertRequest .= '('.implode(',', $value).'),';
            }
            $insertRequest .= rtrim($insertRequest, ',').';';
            $this->logger->info('Mass match | Insert mass matchings | '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            $this->entityManager->getConnection()->prepare($insertRequest)->execute();
        }

        $mass->setStatus(Mass::STATUS_MATCHED);
        $mass->setCalculatedDate(new \Datetime());
        $this->entityManager->persist($mass);
        $this->entityManager->flush();

        // Send an email to notify the operator that the matching is over
        // $event = new MassMatchedEvent($mass);
        // $this->eventDispatcher->dispatch(MassMatchedEvent::NAME, $event);

        $this->logger->info('Mass match | Creating matches records end '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
    }

    private function geoCode(string $input)
    {
        try {
            return $this->createAddressFromLocation($this->geocoder->geocodeQuery(GeocodeQuery::create($input))->first());
        } catch (Exception $e) {
            return false;
        }
    }

    private function createAddressFromLocation(Location $location): Address
    {
        $address = new Address();

        if ($location->getCoordinates() && $location->getCoordinates()->getLatitude()) {
            $address->setLatitude((string) $location->getCoordinates()->getLatitude());
        }
        if ($location->getCoordinates() && $location->getCoordinates()->getLongitude()) {
            $address->setLongitude((string) $location->getCoordinates()->getLongitude());
        }
        $address->setHouseNumber($location->getStreetNumber());
        $address->setStreet($location->getStreetName());
        $address->setStreetAddress($location->getStreetName() ? trim(($location->getStreetNumber() ? $location->getStreetNumber() : '').' '.$location->getStreetName()) : null);
        $address->setSubLocality($location->getSubLocality());
        $address->setAddressLocality($location->getLocality());
        foreach ($location->getAdminLevels() as $level) {
            switch ($level->getLevel()) {
                case 1:
                    $address->setLocalAdmin($level->getName());

                    break;

                case 2:
                    $address->setCounty($level->getName());

                    break;

                case 3:
                    $address->setMacroCounty($level->getName());

                    break;

                case 4:
                    $address->setRegion($level->getName());

                    break;

                case 5:
                    $address->setMacroRegion($level->getName());

                    break;
            }
        }
        $address->setPostalCode($location->getPostalCode());
        if ($location->getCountry() && $location->getCountry()->getName()) {
            $address->setAddressCountry($location->getCountry()->getName());
        }
        if ($location->getCountry() && $location->getCountry()->getCode()) {
            $address->setCountryCode($location->getCountry()->getCode());
        }
        // add layer if handled by the provider
        // if (method_exists($location, 'getLayer')) {
        //     $address->setLayer($this->getLayer($location->getLayer()));
        // }
        // add venue if handled by the provider
        if (method_exists($location, 'getVenue')) {
            $address->setVenue($location->getVenue());
        }
        if ((method_exists($location, 'getEstablishment')) && (null != $location->getEstablishment())) {
            $address->setVenue($location->getEstablishment());
        }
        if ((method_exists($location, 'getPointOfInterest')) && (null != $location->getPointOfInterest())) {
            $address->setVenue($location->getPointOfInterest());
        }

        $address->setProvidedBy($location->getProvidedBy());

        if (method_exists($location, 'getDistance')) {
            if (!is_null($location->getDistance())) {
                $address->setDistance($location->getDistance());
            }
        }

        return $address;
    }

    /**
     * Generates a filename and removes the extension.
     *
     * @param Mass $mass The mass for which we want a filename
     *
     * @return string The filename
     */
    private function generateFilename(Mass $mass)
    {
        $date = new \Datetime();
        if ($mass->getOriginalName()) {
            return $this->fileManager->sanitize($date->format('YmdHis').'-'.substr($mass->getOriginalName(), 0, strrpos($mass->getOriginalName(), '.')));
        }

        return $this->fileManager->sanitize($date->format('YmdHis').'-'.substr($mass->getFile()->getClientOriginalName(), 0, strrpos($mass->getFile()->getClientOriginalName(), '.')));
    }

    /**
     * Treat a mass file.
     *
     * @param Mass $mass The mass to treat
     *
     * @return Mass The mass treated
     */
    private function treatMass(Mass $mass)
    {
        $this->logger->info('Mass match | Treating import file '.$mass->getFileName().' start'.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // we get the validated data from the file
        $data = $this->getData($mass);

        if (count($data->getErrors()) > 0) {
            // the are errors in the file
            $mass->setErrors($data->getErrors());
        } else {
            // we import the persons
            $this->importPersons($data, $mass);
        }

        $this->logger->info('Mass match | Treating import file '.$mass->getFileName().' end'.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));

        // we return the mass object
        return $mass;
    }

    /**
     * Get The validated data from the file.
     */
    private function getData(Mass $mass): MassData
    {
        $this->logger->info('Mass match | Treating import file '.$mass->getFileName().', getting data start'.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
        $this->logger->info('Mass match | Treating import file '.$mass->getFileName().', '.$mass->getMimeType());

        // if it's a plain text we try to guess the real mimetype
        if (self::MIMETYPE_PLAIN == $mass->getMimeType()) {
            $mass->setMimeType($this->guessMimeType('.'.$this->params['folder'].$mass->getFileName()));
        }

        switch ($mass->getMimeType()) {
            case self::MIMETYPE_ZIP:
                return $this->getDataFromZip('.'.$this->params['folder'].$mass->getFileName());

                break;

            case self::MIMETYPE_APPLICATION_XML:
            case self::MIMETYPE_TEXT_XML:
                return $this->getDataFromXml('.'.$this->params['folder'].$mass->getFileName());

                break;

            case self::MIMETYPE_CSV:
                return $this->getDataFromCsv('.'.$this->params['folder'].$mass->getFileName());

                break;
                // case self::MIMETYPE_JSON:
                //     return $this->getDataFromJson('.' . $this->params['folder'] . $mass->getFileName());
                //     break;
            default:
                $massDataReturn = new MassData();
                $errors[] = [
                    'code' => '',
                    'file' => basename('.'.$this->params['folder'].$mass->getFileName()),
                    'line' => 1,
                    'message' => 'This file type is not accepted (only .csv)',
                ];
                $massDataReturn->setErrors($errors);

                return $massDataReturn;

            break;
        }
    }

    /**
     * Try to guess the real mimetype based on the extension of the file.
     *
     * @param string $filename The file
     *
     * @return null|string
     */
    private function guessMimeType(string $filename)
    {
        switch (strtolower($this->fileManager->getExtension($filename))) {
            case 'txt':
            case 'csv':
                return self::MIMETYPE_CSV;

                break;

            case 'xml':
                return self::MIMETYPE_APPLICATION_XML;

                break;

            case 'json':
                return self::MIMETYPE_JSON;

                break;

            default:
                throw new MassException('This file type is not accepted');

                break;
        }
    }

    /**
     * Get data from a zip file.
     *
     * @param string $zip The filename
     *
     * @return MassData
     */
    private function getDataFromZip(string $zip)
    {
        $zipArchive = new \ZipArchive();
        if (true === $zipArchive->open($zip)) {
            if ($zipArchive->numFiles > 1) {
                throw new MassException('Zip file can contain only a single file');
            }
            $filename = $zipArchive->getNameIndex(0);
            if ($zipArchive->extractTo('.'.$this->params['temp'], $filename)) {
                $mimeType = mime_content_type('.'.$this->params['temp'].$filename);
                if (self::MIMETYPE_PLAIN == $mimeType) {
                    $mimeType = $this->guessMimeType('.'.$this->params['temp'].$filename);
                }

                switch ($mimeType) {
                    case self::MIMETYPE_APPLICATION_XML:
                    case self::MIMETYPE_TEXT_XML:
                        return $this->getDataFromXml('.'.$this->params['temp'].$filename, true);

                        break;

                    case self::MIMETYPE_CSV:
                        return $this->getDataFromCsv('.'.$this->params['temp'].$filename, true);

                        break;
                        // case self::MIMETYPE_JSON:
                        //     return $this->getDataFromJson('.' . $this->params['temp'] . $filename, true);
                        //     break;
                    default:
                        throw new MassException('The extracted file type is not accepted');

                        break;
                }
            } else {
                throw new MassException('Cannot extract file');
            }
        } else {
            throw new MassException('Cannot open file');
        }
    }

    /**
     * Get data from a XML file.
     *
     * @param string $xml  The filename
     * @param bool   $temp If the file is temporary
     *
     * @return MassData
     */
    private function getDataFromXml(string $xml, $temp = false)
    {
        $error = false;
        $errors = false;

        // we try to validate the xml
        $validator = new DomValidator($this->params['xml_schema'], $xml);
        if (!$validator->validate()) {
            // errors in the xml
            $error = true;
            $errors = $validator->getErrors();
        }

        // we create a new MassData object to return
        $massData = new MassData();

        if (is_array($errors) && count($errors) > 0) {
            // we have errors in xml : we stop here
            $massData->setErrors($errors);
            // the file was temporary we remove it
            if ($temp) {
                unlink($xml);
            }

            return $massData;
        }
        if ($error) {
            // other error, we stop
            // the file was temporary we remove it
            if ($temp) {
                unlink($xml);
            }

            throw new MassException('Cannot open file');
        }

        // no errors, we parse the xml to get the persons
        $data = simplexml_load_file($xml);
        $persons = [];
        foreach ($data->person as $person) {
            $massPerson = new MassPerson();
            $massPerson->setGivenId($person->givenId);
            $massPerson->setGivenName($person->givenName);
            $massPerson->setFamilyName($person->familyName);
            $personalAddress = new Address();
            $personalAddress->setHouseNumber($person->personalAddress->houseNumber);
            $personalAddress->setStreet($person->personalAddress->street);
            $personalAddress->setPostalCode($person->personalAddress->postalCode);
            $personalAddress->setAddressLocality($person->personalAddress->addressLocality);
            $massPerson->setPersonalAddress($personalAddress);
            $workAddress = new Address();
            $workAddress->setHouseNumber($person->workAddress->houseNumber);
            $workAddress->setStreet($person->workAddress->street);
            $workAddress->setPostalCode($person->workAddress->postalCode);
            $workAddress->setAddressLocality($person->workAddress->addressLocality);
            $massPerson->setWorkAddress($workAddress);
            $massPerson->setOutwardTime($person->outwardTime);
            $massPerson->setReturnTime($person->returnTime);
            $massPerson->setDriver($person->driver);
            $massPerson->setPassenger($person->passenger);
            $persons[] = $massPerson;
        }
        $massData->setData($persons);

        // the file was temporary we remove it
        if ($temp) {
            unlink($xml);
        }

        return $massData;
    }

    /**
     * Get data from a CSV file.
     *
     * @param string $csv  The filename
     * @param bool   $temp If the file is temporary
     *
     * @return MassData
     */
    private function getDataFromCsv(string $csv, $temp = false)
    {
        $error = false;
        $errors = [];
        $fields = [];

        // we check the encoding of the file
        if (!mb_detect_encoding(file_get_contents($csv), 'UTF-8', true)) {
            $error = true;
            $errors[] = [
                'code' => '',
                'file' => basename($csv),
                'line' => 1,
                'message' => 'The file must be UTF-8 encoded',
            ];
        }

        // we get the schema fields
        if ($file = fopen($this->params['csv_schema'], 'a+')) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                for ($i = 0; $i < count($tab); ++$i) {
                    $fields[] = $tab[$i];
                }
            }
        }

        // we try to validate the whole file
        $persons = [];
        $line = 0;
        if ($file = fopen($csv, 'a+')) {
            while ($tab = fgetcsv($file, 4096, ';')) {
                ++$line;
                if (count($tab) != count($fields)) {
                    $error = true;
                    $errors[] = [
                        'code' => '',
                        'file' => basename($csv),
                        'line' => $line,
                        'message' => 'Nombre de champs incorrect',
                    ];
                }
                $massPerson = new MassPerson();
                $personalAddress = new Address();
                $workAddress = new Address();
                for ($i = 0; $i < count($tab); ++$i) {
                    $setter = 'set'.ucwords($fields[$i]);

                    if ('personalAddress.' == substr($fields[$i], 0, 16)) {
                        $setter = 'set'.ucwords(substr($fields[$i], 16));
                        if (method_exists($personalAddress, $setter)) {
                            $personalAddress->{$setter}($tab[$i]);
                        }
                    } elseif ('workAddress.' == substr($fields[$i], 0, 12)) {
                        $setter = 'set'.ucwords(substr($fields[$i], 12));
                        if (method_exists($workAddress, $setter)) {
                            $workAddress->{$setter}($tab[$i]);
                        }
                    } elseif ('outwardTime' == $fields[$i] && '' !== $tab[$i]) {
                        $outwardtime = \DateTime::createFromFormat('H:i', $tab[$i]);
                        if (!$outwardtime) {
                            $error = true;
                            $errors[] = [
                                'code' => '',
                                'file' => basename($csv),
                                'line' => $line,
                                'message' => "Date d'aller incorrecte",
                            ];
                        } else {
                            $massPerson->setOutwardTime($outwardtime->format('H:i:s'));
                        }
                    } elseif ('outwardTime' == $fields[$i] && '' == $tab[$i]) {
                        $massPerson->setOutwardTime(self::DEFAULT_OUTWARD_TIME);
                    } elseif ('returnTime' == $fields[$i] && '' !== $tab[$i]) {
                        $returntime = \DateTime::createFromFormat('H:i', $tab[$i]);
                        if (!$returntime) {
                            $error = true;
                            $errors[] = [
                                'code' => '',
                                'file' => basename($csv),
                                'line' => $line,
                                'message' => 'Date de retour incorrecte',
                            ];
                        } else {
                            $massPerson->setReturnTime($returntime->format('H:i:s'));
                        }
                    } elseif ('returnTime' == $fields[$i] && '' == $tab[$i]) {
                        $massPerson->setReturnTime(self::DEFAULT_RETURN_TIME);
                    } elseif (method_exists($massPerson, $setter)) {
                        $massPerson->{$setter}($tab[$i]);
                    }
                }
                $massPerson->setPersonalAddress($personalAddress);
                $massPerson->setWorkAddress($workAddress);

                $validationErrors = $this->validator->validate($massPerson, null, ['mass']);
                if (count($validationErrors) > 0) {
                    foreach ($validationErrors as $validationError) {
                        $errors[] = [
                            'code' => '',
                            'file' => basename($csv),
                            'line' => $line,
                            'message' => (string) $validationError,
                        ];
                    }
                } else {
                    $persons[] = $massPerson;
                }
            }

            // we create a new MassData object to return
            $massData = new MassData();

            if (count($errors) > 0) {
                // we have errors in csv : we stop here
                $massData->setErrors($errors);
                // the file was temporary we remove it
                if ($temp) {
                    unlink($csv);
                }

                return $massData;
            }
            if ($error) {
                // other error, we stop
                // the file was temporary we remove it
                if ($temp) {
                    unlink($csv);
                }

                throw new MassException('Cannot open file');
            }

            $massData->setData($persons);

            // the file was temporary we remove it
            if ($temp) {
                unlink($csv);
            }

            return $massData;
        }
        $errors = true;

        // the file was temporary we remove it
        if ($temp) {
            unlink($csv);
        }

        if ($errors) {
            throw new MassException('Cannot open file');
        }
    }

    /**
     * Get data from a JSON file.
     *
     * @param string $json The filename
     * @param bool   $temp If the file is temporary
     *
     * @return MassData
     */
    private function getDataFromJson(string $json, $temp = false)
    {
        $errors = false;
        // the file was temporary we remove it
        if ($temp) {
            unlink($json);
        }

        if ($errors) {
            throw new MassException('Cannot open file');
        }
    }

    /**
     * Import persons.
     *
     * @param MassData $data The data to import
     * @param Mass     $mass The parent Mass object
     */
    private function importPersons(MassData $data, Mass $mass)
    {
        // the data property of the MassData contains the MassPerson objects
        foreach ($data->getData() as $massPerson) {
            $massPerson->setMass($mass);
            $this->entityManager->persist($massPerson);
        }
        $this->entityManager->flush();
    }

    /**
     * Check if 2 bounding boxes have collision.
     *
     * @return bool
     */
    private function hasCollisions(array $bbox1, array $bbox2)
    {
        //return true;
        // todo : refactor the values to be strictly positive, to avoid problems with negative coordinates
        $x1 = $bbox1[0];
        $y1 = $bbox1[1];
        $w1 = $bbox1[2] - $bbox1[0];
        $h1 = $bbox1[3] - $bbox1[1];
        $x2 = $bbox2[0];
        $y2 = $bbox2[1];
        $w2 = $bbox2[2] - $bbox2[0];
        $h2 = $bbox2[3] - $bbox2[1];

        return ($x1 < ($x2 + $w2)) && (($x1 + $w1) > $x2) && ($y1 < ($y2 + $h2)) && (($h1 + $y1) > $y2);
    }

    /**
     * Calculate the overlap ratio between 2 bounding boxes.
     */
    private function overlap_ratio(array $bbox1, array $bbox2)
    {
        $surface1 = ($bbox1[2] - $bbox1[0]) * ($bbox1[3] - $bbox1[1]);
        $surface2 = ($bbox2[2] - $bbox2[0]) * ($bbox2[3] - $bbox2[1]);
        $surface_intersect = max(0, min($bbox1[2], $bbox2[2]) - max($bbox1[0], $bbox2[0])) * max(0, min($bbox1[3], $bbox2[3]) - max($bbox1[1], $bbox2[1]));
        $surface_union = $surface1 + $surface2 - $surface_intersect;

        return $surface_intersect / $surface_union;
    }

    private function print_mem($id)
    {
        // Currently used memory
        $mem_usage = memory_get_usage();

        // Peak memory usage
        $mem_peak = memory_get_peak_usage();
        $this->logger->info($id.' The script is now using: '.round($mem_usage / 1024).'KB of memory.');
        $this->logger->info($id.' Peak usage: '.round($mem_peak / 1024).'KB of memory.');
    }
}

/**
 * XML validator.
 */
class DOMValidator
{
    public $errors;
    protected $schema;
    protected $xml;

    /**
     * Validation Class constructor Instantiating DOMDocument.
     *
     * @param \DOMDocument $handler [description]
     * @param mixed        $schema
     * @param mixed        $xml
     */
    public function __construct($schema, $xml)
    {
        $this->handler = new \DOMDocument('1.0', 'utf-8');
        $this->schema = $schema;
        $this->xml = $xml;
    }

    /**
     * Validate Incoming Feeds against Listing Schema.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function validate()
    {
        if (!class_exists('DOMDocument')) {
            throw new MassException("'DOMDocument' class not found!");

            return false;
        }
        if (!file_exists($this->schema)) {
            throw new MassException('Schema is missing, please add schema to schema property');

            return false;
        }
        libxml_use_internal_errors(true);
        if (!($fp = fopen($this->xml, 'r'))) {
            throw new MassException('Cannot open file');
        }

        $contents = fread($fp, filesize($this->xml));
        fclose($fp);

        $this->handler->loadXML($contents, LIBXML_NOBLANKS);
        if (!$this->handler->schemaValidate($this->schema)) {
            $this->errors = $this->libxmlGetErrors();
        } else {
            return true;
        }
    }

    /**
     * Get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param \libXMLError object $error
     *
     * @return array
     */
    private function libxmlGetError($error)
    {
        return [
            'code' => $error->code,
            'file' => basename($this->xml),
            'line' => $error->line,
            'message' => trim($error->message),
        ];
    }

    /**
     * @return array
     */
    private function libxmlGetErrors()
    {
        $errors = libxml_get_errors();
        $result = [];
        foreach ($errors as $error) {
            $result[] = $this->libxmlGetError($error);
        }
        libxml_clear_errors();

        return $result;
    }
}
