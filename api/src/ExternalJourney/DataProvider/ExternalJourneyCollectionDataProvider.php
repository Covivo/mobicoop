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
use App\ExternalJourney\Entity\ExternalJourney;
use Symfony\Component\HttpFoundation\RequestStack;

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
        $apiUrl = 'http://api.test.ouestgo.fr';
        $apiKey= 'rdex_mobicoop';//public apikey
        $privateKey = 'rdex_mobicoop_uijdhdh4822444;jhduudd854128AJSjhhh-42';

        /*$apiUrl = 'http://www.covivo.eu';
        $apiKey= 'rdex_itinisere';//public apikey
        $privateKey = 'rdex_itinisere_&aer-açàuhb2-/!.1a51a-541?!auigyzur-42';*/

        //We get parameters here
        $this->request->get("driver");
        $this->request->get("passenger");
        $this->request->get("from_latitude");
        $this->request->get("from_longitude");
        $this->request->get("to_latitude");
        $this->request->get("to_longitude");
        //then we set these parameters
        $searchParameters  = [
            'driver'  => [
                'state'   => $this->request->get("driver") //1
            ],
            'passenger' => [
                'state'   => $this->request->get("passenger") //1
            ],
            'from'    => [
                'latitude'  => $this->request->get("from_latitude"), //Nancy=48.69278
                'longitude' => $this->request->get("from_longitude") //6.18361
            ],
            'to'    => [
                'latitude'  => $this->request->get("to_latitude"),//Metz=49.11972
                'longitude' => $this->request->get("to_longitude")//6.17694
            ],
            //optional
            //'frequency' => 'regular',
            'outward' => []
        ];

        $data = array(
           'timestamp' => time(),
           'apikey'    => $apiKey,
           'p'         => $searchParameters //optional if POST
        );

        // Construct the requested url
        $url = $apiUrl.'/restapi/journeys.json?'.http_build_query($data);
        $signature = hash_hmac('sha256', $url, $privateKey);
        $signedUrl = $url.'&signature='.$signature;

        //Request the url
        $data = file_get_contents($signedUrl);
        //echo ($driver);
        //echo(gettype(json_decode($data)));
        return json_decode($data, true);
    }
}
