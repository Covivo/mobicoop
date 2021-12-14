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

use App\ExternalJourney\Entity\ExternalJourneyProvider;
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
        $client = new Client([
            //10s because i'm working on long requests but you can change it
            'timeout'  => 10.0,
        ]);
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
            $carpool = [
                "external" => true,
                "externalProvider" => $this->getCurrentProvider()->getName(),
                'matchingId' => null,
                'carpoolerId' => null,
                'carpoolerGivenName' => null,
                'carpoolerFamilyName' => null,
                'carpoolerAvatar' => null,
                'frequency' => null,
                // type is used to determine if the carpool has only an outward or also a return
                'type' => null,
                'passenger' => null,
                'driver' => null,
                'solidaryExclusive' => null,
                'fromDate' => null,
                'fromTime' => null,
                'toDate' => null,
                'carpoolerFromDate' => null,
                'carpoolerFromTime' => null,
                'carpoolerToDate' => null
            ];

            $results[] = $carpool;
        }
        
        return $results;
    }
}
