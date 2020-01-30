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

namespace App\ExternalJourney\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Result;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Client;

use App\ExternalJourney\Entity\ExternalJourney;
use App\ExternalJourney\Service\ExternalJourneyManager;
use App\Geography\Entity\Address;
use App\User\Entity\User;

/**
 * Collection data provider for External Journey entity.
 *
 * Automatically associated to External Journey entity thanks to autowiring (see 'supports' method).
 *
 * @author Sofiane Belaribi <sofiane.belaribi@covivo.eu>
 *
 */
final class ExternalJourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private const EXTERNAL_JOURNEY_HASH = "sha256";         // hash algorithm

    private $externalJourneyManager;

    protected $request;

    public function __construct(RequestStack $requestStack, ExternalJourneyManager $externalJourneyManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->externalJourneyManager = $externalJourneyManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExternalJourney::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        // initialize client API for any request
        $client = new Client([
            //10s because i'm working on long requests but you can change it
            'timeout'  => 10.0,
        ]);
        // we collect search parameters here
        $providerName = $this->request->get("provider");
        $driver = $this->request->get("driver");
        $passenger = $this->request->get("passenger");
        $fromLatitude = $this->request->get("from_latitude");
        $fromLongitude = $this->request->get("from_longitude");
        $toLatitude = $this->request->get("to_latitude");
        $toLongitude = $this->request->get("to_longitude");
        $outwardMinDate = $this->request->get("outward_mindate");
        $outwardMaxDate = $this->request->get("outward_maxdate");
        $frequency = $this->request->get("frequency");
        $rawJson = $this->request->get("rawJson");
        $days = ["monday","tuesday","wednesday","thursday","friday","saturday","sunday"];

        // then we set these parameters
        $searchParameters  = [
            'driver'  => [
                'state'   => $driver
            ],
            'passenger' => [
                'state'   => $passenger
            ],
            'from'    => [
                'latitude'  => $fromLatitude,
                'longitude' => $fromLongitude
            ],
            'to'    => [
                'latitude'  => $toLatitude,
                'longitude' => $toLongitude
            ]
        ];


        if ($frequency!=="" && ($frequency=="regular" || $frequency=="punctual")) {
            $searchParameters['frequency'] = $frequency;
        }

        if ($outwardMinDate!=="") {
            $searchParameters['outward']['mindate'] = $outwardMinDate;
        }
        if ($outwardMaxDate!=="") {
            $searchParameters['outward']['maxdate'] = $outwardMaxDate;
        }


        // Days treatment for regular journeys
        // which days
        foreach ($days as $day) {
            $currentday = $this->request->get("days_".$day);
            if ($currentday !== "") {
                $searchParameters['days'][$day] = $currentday;
            } else {
                $searchParameters['days'][$day] = 0;
            }
        }

        // mintime and maxtime for days
        foreach ($days as $day) {
            $mintime = $this->request->get($day."_mintime");
            if ($mintime!=="") {
                $searchParameters['outward'][$day]["mintime"] = $mintime;
            }
            $maxtime = $this->request->get($day."_maxtime");
            if ($maxtime!=="") {
                $searchParameters['outward'][$day]["maxtime"] = $maxtime;
            }
        }

        // @todo error management (api not responding, bad parameters...)
        foreach ($this->externalJourneyManager->getProviders() as $provider) {
            if ($provider->getName() == $providerName) {
                $query = array(
                    'timestamp' => time(),
                    'apikey'    => $provider->getApiKey(),
                    'p'         => $searchParameters
                );
                // construct the requested url
                $url = $provider->getUrl().'/'.$provider->getResource().'?'.http_build_query($query);
                $signature = hash_hmac(self::EXTERNAL_JOURNEY_HASH, $url, $provider->getPrivateKey());
                $signedUrl = $url.'&signature='.$signature;
                // request url
                $data = $client->request('GET', $signedUrl);
                $data = $data->getBody()->getContents();

                if ($data!=="") {
                    if ($rawJson==1) {
                        // rawJson flag set. We return RDEX format.
                        return json_decode($data, true);
                    } else {
                        // No rawJson flag set or set to 0. We return array of Carpool -> Result.
                        return $this->createResultFromRDEX($data);
                    }
                } else {
                    return [];
                }
            }
        }
        return [];
    }

    public function createResultFromRDEX($data): array
    {
        $results = [];
        $journeys = json_decode($data, true);
        foreach ($journeys as $journey) {
            $currentJourney = $journey['journeys'];
            $result = new Result();

            // The carpooler
            $carpooler = new User();
            $carpooler->setGivenName($currentJourney['driver']['alias']);
            $carpooler->setGender(User::GENDER_FEMALE);
            if ($currentJourney['driver']['gender']==="male") {
                $carpooler->setGender(User::GENDER_MALE);
            }
            $result->setCarpooler($carpooler);



            // Days checked
            $result->setMonCheck($currentJourney['days']['monday']);
            $result->setTueCheck($currentJourney['days']['tuesday']);
            $result->setWedCheck($currentJourney['days']['wednesday']);
            $result->setThuCheck($currentJourney['days']['thursday']);
            $result->setFriCheck($currentJourney['days']['friday']);
            $result->setSatCheck($currentJourney['days']['saturday']);
            $result->setSunCheck($currentJourney['days']['sunday']);

            // We check all times and if they are all the same, we set the time of the Result
            $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
            $currentTime = "";
            $returnTime = true;
            $time = "";
            foreach ($days as $day) {
                // Only for checked days
                if ($currentJourney['days'][$day]) {
                    $time = $this->middleHour($currentJourney['outward'][$day]['mintime'], $currentJourney['outward'][$day]['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']);
                    
                    // Only the first time to init the reference
                    if ($currentTime==="") {
                        $currentTime=$time;
                    }
                    
                    if ($currentTime !== $time) {
                        $returnTime = false;
                        break;
                    }
                }
            }

            // Regular/Punctual treatment
            if ($currentJourney['frequency']==="regular") {
                // REGULAR
                $result->setFrequency(Criteria::FREQUENCY_REGULAR);
                $result->setOutwardTime(($time!=="") ? $time : null);
            } else {
                // PUNCTUAL
                $result->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $result->setDate(new \Datetime($currentJourney['outward']['mindate']));
            }

            // Origin
            $origin = new Address();
            $origin->setLatitude($currentJourney['from']['latitude']);
            $origin->setLongitude($currentJourney['from']['longitude']);
            $origin->setStreetAddress($currentJourney['from']['address']);
            $origin->setPostalCode($currentJourney['from']['postalcode']);
            $origin->setAddressLocality($currentJourney['from']['city']);
            $origin->setAddressCountry($currentJourney['from']['country']);
            $result->setOrigin($origin);

            // Destination
            $destination = new Address();
            $destination->setLatitude($currentJourney['to']['latitude']);
            $destination->setLongitude($currentJourney['to']['longitude']);
            $destination->setStreetAddress($currentJourney['to']['address']);
            $destination->setPostalCode($currentJourney['to']['postalcode']);
            $destination->setAddressLocality($currentJourney['to']['city']);
            $destination->setAddressCountry($currentJourney['to']['country']);
            $result->setDestination($destination);


            // price - seats - distance - duration
            $result->setTime(($time!=="") ? $time : null);
            $result->setRoundedPrice(round(($currentJourney['distance'] / 1000) * $currentJourney['cost']['variable'], 2));
            $result->setSeats($currentJourney['driver']['seats']);

            // return trip ?
            $result->setReturn(false);
            if ($currentJourney["type"]==="round-trip") {
                $result->setReturn(true);
            }

            $results[] = $result;
        }

        return $results;
    }

    public function middleHour($heureMin, $heureMax, $dateMin, $dateMax)
    {
        $min = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMin . " " . $heureMin, new \DateTimeZone('UTC'));
        $mintime = $min->getTimestamp();
        $max = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMax . " " . $heureMax, new \DateTimeZone('UTC'));
        $maxtime = $max->getTimestamp();
        $marge = ($maxtime - $mintime) / 2;
        $middleHour = $mintime + $marge;
        $returnHour = new \DateTime();
        $returnHour->setTimestamp($middleHour);
        return $returnHour;
    }
}
