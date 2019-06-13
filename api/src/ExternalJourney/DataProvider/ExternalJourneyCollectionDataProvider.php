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
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Client;

use App\ExternalJourney\Entity\ExternalJourney;
use App\ExternalJourney\Service\ExternalJourneyManager;

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
                    return json_decode($data, true);
                } else {
                    return [];
                }
            }
        }
        return [];
    }
}
