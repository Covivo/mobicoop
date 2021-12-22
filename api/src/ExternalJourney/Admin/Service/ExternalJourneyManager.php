<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\ExternalJourney\Admin\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\ExternalJourney\Entity\ExternalJourneyProvider;
use App\Geography\Entity\Address;
use App\Rdex\Entity\RdexJourney;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

/**
 * External journey service in administration context
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalJourneyManager
{
    private const EXTERNAL_JOURNEY_HASH = "sha256";         // hash algorithm

    private $operator;
    private $clients;
    private $providers;

    private $data;
    private $currentProvider;

    public function __construct(?array $operator = [], ?array $clients = [], ?array $providers = [])
    {
        $this->operator = $operator;
        $this->clients = $clients;
        foreach ($providers as $providerName=>$details) {
            $provider = new ExternalJourneyProvider();
            $provider->setName($providerName);
            $provider->setUrl($details['url']);
            $provider->setResource($details['resource']);
            $provider->setApiKey($details['api_key']);
            $provider->setPrivateKey($details['private_key']);
            $this->providers[] = $provider;
        }
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data)
    {
        $this->data = $data;
    }

    public function getCurrentProvider(): ExternalJourneyProvider
    {
        return $this->currentProvider;
    }

    public function setCurrentProvider(ExternalJourneyProvider $currentProvider)
    {
        $this->currentProvider = $currentProvider;
    }

    public function getExternalJourneys(Request $request, array $params): array
    {
        $this->params = $params;

        // initialize client API for any request
        $client = new Client();
        // we collect search parameters here
        $providerName = $request->get("provider");
        $driver = $request->get("driver");
        $passenger = $request->get("passenger");
        $fromLatitude = $request->get("from_latitude");
        $fromLongitude = $request->get("from_longitude");
        $toLatitude = $request->get("to_latitude");
        $toLongitude = $request->get("to_longitude");
        $outwardMinDate = $request->get("outward_mindate");
        $outwardMaxDate = $request->get("outward_maxdate");
        $frequency = $request->get("frequency");
        $rawJson = $request->get("rawJson");
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
            $currentday = $request->get("days_".$day);
            if ($currentday !== "") {
                $searchParameters['days'][$day] = $currentday;
            } else {
                $searchParameters['days'][$day] = 0;
            }
        }

        // mintime and maxtime for days
        foreach ($days as $day) {
            $mintime = $request->get($day."_mintime");
            if ($mintime!=="") {
                $searchParameters['outward'][$day]["mintime"] = $mintime;
            }
            $maxtime = $request->get($day."_maxtime");
            if ($maxtime!=="") {
                $searchParameters['outward'][$day]["maxtime"] = $maxtime;
            }
        }

        $aggregatedResults = [];
        $providers = $this->getProviders();
        
        // If a provider is given in parameters, we take only this one
        // Otherwise, we use all providers
        if ($providerName !== '') {
            foreach ($providers as $provider) {
                if ($provider->getName() == $providerName) {
                    $providers = [$provider];
                }
            }
        }
        
        // @todo error management (api not responding, bad parameters...)
        foreach ($providers as $provider) {
            $this->setCurrentProvider($provider);

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
            $this->setData($data->getBody()->getContents());

            if ($this->data!=="") {
                if ($rawJson==1) {
                    // rawJson flag set. We return RDEX format.
                    $aggregatedResults = json_decode($this->getData(), true);
                } else {
                    // No rawJson flag set or set to 0. We return an array
                    foreach ($this->createCarpoolFromRDEX() as $currentResult) {
                        $aggregatedResults[] = $currentResult;
                    }
                }
            }
        }
        return $aggregatedResults;
    }

    private function createCarpoolFromRDEX(): array
    {
        $results = [];
        $journeys = json_decode($this->getData(), true);
        foreach ($journeys as $journey) {
            $currentJourney = $journey['journeys'];
            
            if (isset($currentJourney['driver']['uuid'])) {
                $currentJourneyCarpooler = $currentJourney['driver'];
            } else {
                $currentJourneyCarpooler = $currentJourney['passenger'];
            }

            // We check all times and if they are all the same, we set the time of the result
            $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
            $currentTime = "";
            $returnTime = true;
            $time = "";
            foreach ($days as $day) {

                // Only for checked days
                if ($currentJourney['days'][$day]  && !is_null($currentJourney['outward'][$day]['mintime'])) {
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

            $carpool = [
                "external" => true,
                "externalProvider" => $this->getCurrentProvider()->getName(),
                "journeyId" => $currentJourney['uuid'],
                'matchingId' => null,
                'carpoolerId' => $currentJourneyCarpooler['uuid'],
                'carpoolerGivenName' => $currentJourneyCarpooler['alias'],
                'carpoolerFamilyName' => null,
                'carpoolerAvatar' => $currentJourneyCarpooler['image'],
                'frequency' => ($currentJourney['frequency']==="regular") ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL,
                'type' => ($currentJourney['type']==RdexJourney::TYPE_ONE_WAY) ? 'oneway' : 'roundtrip',
                'passenger' => (!is_null($currentJourney['passenger'])) ? 1 : 0,
                'driver' => (!is_null($currentJourney['driver'])) ? 1 : 0,
                'solidaryExclusive' => null,
                'fromDate' => $currentJourney['outward']['mindate'],
                'fromTime' => ($time!=="") ? $time : null,
                'toDate' => null,
                'carpoolerFromDate' => null,
                'carpoolerFromTime' => null,
                'carpoolerToDate' => null
            ];

            if ($carpool['frequency'] == Criteria::FREQUENCY_REGULAR) {
                $carpool['carpoolerSchedule'] = [];
                $carpool['schedule'] = [
                    'mon' => $currentJourney['days']['monday'] == 1 && !is_null($currentJourney['outward']['monday']['mintime']) ? $this->middleHour($currentJourney['outward']['monday']['mintime'], $currentJourney['outward']['monday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'tue' => $currentJourney['days']['tuesday'] == 1 && !is_null($currentJourney['outward']['tuesday']['mintime']) ? $this->middleHour($currentJourney['outward']['tuesday']['mintime'], $currentJourney['outward']['tuesday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'wed' => $currentJourney['days']['wednesday'] == 1 && !is_null($currentJourney['outward']['wednesday']['mintime']) ? $this->middleHour($currentJourney['outward']['wednesday']['mintime'], $currentJourney['outward']['wednesday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'thu' => $currentJourney['days']['thursday'] == 1 && !is_null($currentJourney['outward']['thursday']['mintime']) ? $this->middleHour($currentJourney['outward']['thursday']['mintime'], $currentJourney['outward']['thursday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'fri' => $currentJourney['days']['friday'] == 1 && !is_null($currentJourney['outward']['friday']['mintime']) ? $this->middleHour($currentJourney['outward']['friday']['mintime'], $currentJourney['outward']['friday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'sat' => $currentJourney['days']['saturday'] == 1 && !is_null($currentJourney['outward']['saturday']['mintime']) ? $this->middleHour($currentJourney['outward']['saturday']['mintime'], $currentJourney['outward']['saturday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false,
                    'sun' => $currentJourney['days']['sunday'] == 1 && !is_null($currentJourney['outward']['sunday']['mintime']) ? $this->middleHour($currentJourney['outward']['sunday']['mintime'], $currentJourney['outward']['sunday']['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']) : false
                ];
            }

            // Origin
            $origin = new Address();
            $origin->setLatitude($currentJourney['from']['latitude']);
            $origin->setLongitude($currentJourney['from']['longitude']);
            $origin->setStreetAddress($currentJourney['from']['address']);
            $origin->setPostalCode(isset($currentJourney['from']['postalcode']) ? $currentJourney['from']['postalcode'] : null);
            $origin->setAddressLocality($currentJourney['from']['city']);
            $origin->setAddressCountry($currentJourney['from']['country']);
            $carpool['origin'] = $origin->jsonSerialize();

            // Destination
            $destination = new Address();
            $destination->setLatitude($currentJourney['to']['latitude']);
            $destination->setLongitude($currentJourney['to']['longitude']);
            $destination->setStreetAddress($currentJourney['to']['address']);
            $destination->setPostalCode(isset($currentJourney['to']['postalcode']) ? $currentJourney['to']['postalcode'] : null);
            $destination->setAddressLocality($currentJourney['to']['city']);
            $destination->setAddressCountry($currentJourney['to']['country']);
            $carpool['destination'] = $destination->jsonSerialize();

            $carpool['detourDuration'] = $currentJourney['duration'];
            $carpool['detourDistance'] = $currentJourney['distance'];


            $results[] = $carpool;
        }
        
        return $results;
    }

    private function middleHour($heureMin, $heureMax, $dateMin, $dateMax)
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
