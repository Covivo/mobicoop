<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\ExternalJourney\Service;

use GuzzleHttp\Client;

class JourneyProviderRdex extends JourneyProvider
{
    private const EXTERNAL_JOURNEY_HASH = 'sha256';         // hash algorithm

    public function getJourneys($provider, $request): array
    {
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
        $time = $request->get('time');
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

        if ('punctual' == $searchParameters['frequency']) {
            if ('' !== $time && !is_null($time)) {
                if (substr_count($time, ':') < 2) {
                    $time .= ':00';
                }
                $day = \DateTime::createFromFormat('Y-m-d', $searchParameters['outward']['mindate']);
                $searchParameters['outward'][strtolower($day->format('l'))]['mintime'] = $time;
            }
        } else {
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
        }
        // initialize client API for any request
        $client = new Client();

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

        try {
            $data = $client->request('GET', $signedUrl, ['connect_timeout' => 30]);
        } catch (\Exception $e) {
            return ['RDEX' => ['providerName' => $provider->getName(), 'journeys' => '']];
        }

        return ['RDEX' => ['providerName' => $provider->getName(), 'journeys' => $data->getBody()->getContents()]];
    }
}
