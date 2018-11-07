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
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;

use App\ExternalJourney\Entity\ExternalJourney;

final class ExternalJourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;

    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExternalJourney::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        $urlPromises = [];
        //initialize client API for any request
        $client = new Client([
            //10s because i'm working on long requests but you can change it
            'timeout'  => 10.0,
        ]);


        //We collect search parameters here
        $provider_name = $this->request->get("provider_name");
        $driver = $this->request->get("driver");
        $passenger = $this->request->get("passenger");
        $from_latitude = $this->request->get("from_latitude");
        $from_longitude = $this->request->get("from_longitude");
        $to_latitude = $this->request->get("to_latitude");
        $to_longitude = $this->request->get("to_longitude");
        //then we set these parameters
        $searchParameters  = [
            'driver'  => [
                'state'   => $driver //1
            ],
            'passenger' => [
                'state'   => $passenger //1
            ],
            'from'    => [
                'latitude'  => $from_latitude, //Nancy=48.69278
                'longitude' => $from_longitude //6.18361
            ],
            'to'    => [
                'latitude'  => $to_latitude,//Metz=49.11972
                'longitude' => $to_longitude//6.17694
            ]
        ];

        //if config.json exists we collect its parameters and request all apis
        $path = "../config.json";
        if (file_exists($path)) {
            //Read config.json
            $provider_list = json_decode(file_get_contents($path), true);
            $truc = array_keys($provider_list["rdexApi"]);

            $dataArray = [];
            foreach ($provider_list["rdexApi"] as $key => $provider) {
                if($key == $provider_name){
                    //Collect provider's parameters
                    $apiUrl = $provider["apiUrl"];
                    $apiKey = $provider["apiKey"];
                    $privateKey = $provider["privateKey"];

                    $query = array(
                        'timestamp' => time(),
                        'apikey'    => $apiKey,
                        'p'         => $searchParameters //optional if POST
                    );

                    //Construct the requested url
                    $url = $apiUrl.'/restapi/journeys.json?'.http_build_query($query);
                    $signature = hash_hmac('sha256', $url, $privateKey);
                    $signedUrl = $url.'&signature='.$signature;

                    //Request url
                    $data = $client->request('GET', $signedUrl);
                    $data = $data->getBody()->getContents();
                    $dataArray = array_merge($dataArray, json_decode($data, true));
                }
            }
            return $dataArray;
        }
        return ["no config.json found"];
    }
}
