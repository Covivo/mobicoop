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

namespace App\DataProvider\Service;

use App\DataProvider\Entity\Response;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\TransferException;

/**
 * Data provider service.
 * Uses an API to retrieve/send data.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class DataProvider
{
    private $client;
    private $resource;
    
    public function __construct($uri,$resource)
    {
        $this->client = new Client([
                'base_uri' => $uri
        ]);
        $this->resource = $resource;
        
    }
    
    /**
     * Get collection operation
     *
     * @param array|null    $params         An array of parameters
     *
     * @return Response The response of the operation.
     */
    public function getCollection(array $params=null): Response
    {
        // @todo : send the params to the request in the json body of the request
        try {
            $clientResponse = $this->client->get($this->resource,['query'=>$params]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(),$clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }
    
}
