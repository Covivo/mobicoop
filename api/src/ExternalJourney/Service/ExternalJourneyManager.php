<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\ExternalJourney\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\ResultRole;
use App\ExternalJourney\Entity\ExternalJourneyProvider;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Request;

/**
 * External journey service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalJourneyManager
{
    private const EXTERNAL_JOURNEY_HASH = 'sha256';         // hash algorithm
    private $operator;
    private $clients;
    private $providers;
    private $params;

    public function __construct(?array $operator = [], ?array $clients = [], ?array $providers = [])
    {
        $this->operator = $operator;
        $this->clients = $clients;
        foreach ($providers as $providerName => $details) {
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
        return (!is_null($this->providers) && is_array($this->providers)) ? $this->providers : [];
    }

    public function getExternalJourneys(Request $request, array $params): array
    {
        $this->params = $params;

        // initialize client API for any request
        $client = new Client();

        // we collect search parameters here
        $providerName = $request->get('provider');
        $driver = $request->get('driver');
        $passenger = $request->get('passenger');
        $fromLatitude = $request->get('from_latitude');
        $fromLongitude = $request->get('from_longitude');
        $toLatitude = $request->get('to_latitude');
        $toLongitude = $request->get('to_longitude');
        $date = $request->get('date');
        $outwardMinDate = $request->get('outward_mindate');
        $outwardMaxDate = $request->get('outward_maxdate');
        $frequency = $request->get('frequency');
        $rawJson = $request->get('rawJson');
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        // then we set these parameters
        $searchParameters = [
            'driver' => [
                'state' => $driver,
            ],
            'passenger' => [
                'state' => $passenger,
            ],
            'from' => [
                'latitude' => $fromLatitude,
                'longitude' => $fromLongitude,
            ],
            'to' => [
                'latitude' => $toLatitude,
                'longitude' => $toLongitude,
            ],
        ];

        if ('' !== $frequency && ('regular' == $frequency || 'punctual' == $frequency)) {
            $searchParameters['frequency'] = $frequency;
        }

        if (!is_null($outwardMinDate) && '' !== $outwardMinDate) {
            $searchParameters['outward']['mindate'] = $outwardMinDate;
        }
        if (!is_null($outwardMaxDate) && '' !== $outwardMaxDate) {
            $searchParameters['outward']['maxdate'] = $outwardMaxDate;
        }
        // Override
        if (!is_null($date) && '' !== $date) {
            $searchParameters['outward']['mindate'] = $date;
            $searchParameters['outward']['maxdate'] = $date;
        }

        // Days treatment for regular journeys
        // which days
        foreach ($days as $day) {
            $currentday = $request->get('days_'.$day);
            if ('' !== $currentday) {
                $searchParameters['days'][$day] = $currentday;
            } else {
                $searchParameters['days'][$day] = 0;
            }
        }

        // mintime and maxtime for days
        foreach ($days as $day) {
            $mintime = $request->get($day.'_mintime');
            if ('' !== $mintime) {
                $searchParameters['outward'][$day]['mintime'] = $mintime;
            }
            $maxtime = $request->get($day.'_maxtime');
            if ('' !== $maxtime) {
                $searchParameters['outward'][$day]['maxtime'] = $maxtime;
            }
        }

        $aggregatedResults = [];
        $providers = $this->getProviders();

        // If a provider is given in parameters, we take only this one
        // Otherwise, we use all providers
        if ('' !== $providerName) {
            foreach ($providers as $provider) {
                if ($provider->getName() == $providerName) {
                    $providers = [$provider];
                }
            }
        }

        // @todo error management (api not responding, bad parameters...)
        foreach ($providers as $provider) {
            $query = [
                'timestamp' => time(),
                'apikey' => $provider->getApiKey(),
                'p' => $searchParameters,
            ];
            // construct the requested url
            $url = $provider->getUrl().'/'.$provider->getResource().'?'.http_build_query($query);
            $signature = hash_hmac(self::EXTERNAL_JOURNEY_HASH, $url, $provider->getPrivateKey());
            $signedUrl = $url.'&signature='.$signature;
            // request url
            $data = $client->request('GET', $signedUrl);
            $data = $data->getBody()->getContents();

            if ('' !== $data) {
                if (1 == $rawJson) {
                    // rawJson flag set. We return RDEX format.
                    $aggregatedResults = json_decode($data, true);
                } else {
                    // No rawJson flag set or set to 0. We return array of Carpool -> Result.
                    foreach ($this->createResultFromRDEX($data, $provider) as $currentResult) {
                        $aggregatedResults[] = $currentResult;
                    }
                }
            }
        }

        return $aggregatedResults;
    }

    private function createResultFromRDEX($data, $provider): array
    {
        $results = [];
        $journeys = json_decode($data, true);
        foreach ($journeys as $journey) {
            $currentJourney = $journey['journeys'];
            $result = new Result();

            // The carpooler
            $carpooler = new User();

            if (isset($currentJourney['driver']['uuid'])) {
                $currentJourneyCarpooler = $currentJourney['driver'];
            } else {
                $currentJourneyCarpooler = $currentJourney['passenger'];
            }

            $carpooler->setId($currentJourneyCarpooler['uuid']);
            $carpooler->setGivenName($currentJourneyCarpooler['alias']);
            $carpooler->setGender(User::GENDER_FEMALE);
            if ('male' === $currentJourneyCarpooler['gender']) {
                $carpooler->setGender(User::GENDER_MALE);
            }
            if (is_null($currentJourneyCarpooler['image'])) {
                foreach (json_decode($this->params['avatarSizes']) as $size) {
                    if (in_array($size, User::AUTHORIZED_SIZES_DEFAULT_AVATAR)) {
                        $carpooler->addAvatar($this->params['avatarDefaultFolder'].$size.'.svg');
                    }
                }
            } else {
                $carpooler->addAvatar($currentJourneyCarpooler['image']);
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
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $currentTime = '';
            $returnTime = true;
            $time = '';
            foreach ($days as $day) {
                // Only for checked days
                if ($currentJourney['days'][$day]) {
                    $time = $this->middleHour($currentJourney['outward'][$day]['mintime'], $currentJourney['outward'][$day]['maxtime'], $currentJourney['outward']['mindate'], $currentJourney['outward']['mindate']);

                    // Only the first time to init the reference
                    if ('' === $currentTime) {
                        $currentTime = $time;
                    }

                    if ($currentTime !== $time) {
                        $returnTime = false;

                        break;
                    }
                }
            }

            // Regular/Punctual treatment
            if ('regular' === $currentJourney['frequency']) {
                // REGULAR
                $result->setFrequency(Criteria::FREQUENCY_REGULAR);
                $result->setOutwardTime(('' !== $time) ? $time : null);

                // We need to find the first valid date
                $firsValidDay = new \DateTime();
                $cptDay = 0;
                while ($cptDay < 6 && !$currentJourney['days'][lcfirst($firsValidDay->format('l'))]) {
                    ++$cptDay;
                    $firsValidDay = new \DateTime('now +'.$cptDay.' days');
                }
                $result->setDate($firsValidDay);
            } else {
                // PUNCTUAL
                $result->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $result->setDate(new \DateTime($currentJourney['outward']['mindate']));
            }

            // Origin
            $origin = new Address();
            $origin->setLatitude($currentJourney['from']['latitude']);
            $origin->setLongitude($currentJourney['from']['longitude']);
            $origin->setStreetAddress($currentJourney['from']['address']);
            $origin->setPostalCode(isset($currentJourney['from']['postalcode']) ? $currentJourney['from']['postalcode'] : null);
            $origin->setAddressLocality($currentJourney['from']['city']);
            $origin->setAddressCountry($currentJourney['from']['country']);
            $result->setOrigin($origin);

            // Destination
            $destination = new Address();
            $destination->setLatitude($currentJourney['to']['latitude']);
            $destination->setLongitude($currentJourney['to']['longitude']);
            $destination->setStreetAddress($currentJourney['to']['address']);
            $destination->setPostalCode(isset($currentJourney['to']['postalcode']) ? $currentJourney['to']['postalcode'] : null);
            $destination->setAddressLocality($currentJourney['to']['city']);
            $destination->setAddressCountry($currentJourney['to']['country']);
            $result->setDestination($destination);

            // price - seats - distance - duration
            $result->setTime(('' !== $time) ? $time : null);
            $result->setRoundedPrice(round(($currentJourney['distance'] / 1000) * $currentJourney['cost']['variable'], 2));
            $result->setSeats(isset($currentJourney['driver']['seats']) ? $currentJourney['driver']['seats'] : 0);

            // return trip ?
            $result->setReturn(false);
            if ('round-trip' === $currentJourney['type']) {
                $result->setReturn(true);
            }

            // We only set resultPassenger and resultDriver for the roles.
            // We don't need the data.
            if (isset($currentJourney['driver']) && !is_null($currentJourney['driver'])) {
                $resultPassenger = new ResultRole();
                $result->setResultPassenger($resultPassenger);
            }
            if (isset($currentJourney['passenger']) && !is_null($currentJourney['passenger'])) {
                $resultDriver = new ResultRole();
                $result->setResultDriver($resultDriver);
            }

            if (!isset($currentJourney['url']) || '' === trim($currentJourney['url'])) {
                $result->setExternalUrl($currentJourney['origin']);
            } else {
                if (false !== strpos($currentJourney['url'], 'http')) {
                    $result->setExternalUrl($currentJourney['url']);
                } else {
                    $result->setExternalUrl('https://'.$currentJourney['url']);
                }
            }

            $result->setExternalOrigin($currentJourney['origin']);
            $result->setExternalOperator($currentJourney['operator']);
            $result->setExternalProvider($provider->getName());
            $result->setExternalJourneyId($currentJourney['uuid']);
            $results[] = $result;
        }

        return $results;
    }

    private function middleHour($heureMin, $heureMax, $dateMin, $dateMax)
    {
        $min = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMin.' '.$heureMin, new \DateTimeZone('UTC'));
        $mintime = $min->getTimestamp();
        $max = \DateTime::createFromFormat('Y-m-d H:i:s', $dateMax.' '.$heureMax, new \DateTimeZone('UTC'));
        $maxtime = $max->getTimestamp();
        $marge = ($maxtime - $mintime) / 2;
        $middleHour = $mintime + $marge;
        $returnHour = new \DateTime();
        $returnHour->setTimestamp($middleHour);

        return $returnHour;
    }
}
