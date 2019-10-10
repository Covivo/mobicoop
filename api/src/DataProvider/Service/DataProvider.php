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
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;

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
    
    /**
     * Constructor.
     *
     * @param string        $uri
     * @param string        $resource   Resource name for normal resource
     */
    public function __construct(string $uri, string $resource)
    {
        $this->client = new Client([
                'base_uri' => $uri
        ]);
        $this->resource = $resource;
    }

    public function setResource($resource){
        $this->resource = $resource;
    }

     /**
     * Get item operation
     *
     * @param int       $id         The id of the item
     *
     * @return Response The response of the operation.
     */
    public function getItem(array $params): Response
    {
        $clientResponse="";
        try {
            $clientResponse = $this->client->get($this->resource."?".http_build_query($params));
            return new Response($clientResponse->getStatusCode(),$clientResponse->getBody()->getContents());
        }
        catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();  
    }
    
    /**
     * Get collection operation
     *
     * @param mixed|null    $params         An array or string of parameters
     *
     * @return Response The response of the operation.
     */
    public function getCollection($params=null): Response
    {
        try {
            $clientResponse = $this->client->get($this->resource, ['query'=>$params]);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
     * Get async collection operation
     *
     * @param mixed|null    $params         An array of parameters
     *
     * @return Response The response of the operation.
     */
    public function getAsyncCollection($params=null): Response
    {
        $promises = [];
        foreach ($params as $key=>$resource) {
            $promises[$key] = $this->client->getAsync($this->resource, ['query'=>$resource]);
        }
        try {
            $results = Promise\unwrap($promises);
            $bodies = [];
            foreach ($results as $key=>$result) {
                $bodies[$key] = $result->getBody();
            }
            return new Response(200, $bodies);
        } catch (ConnectException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }

    /**
    * Get collection operation
    *
    * @param mixed|null    $params         An array or string of parameters
    *
    * @return Response The response of the operation.
    */
    public function postCollection($body=null, $headers=null, $params=null): Response
    {
        try {
            $options=[];
            if ($params) {
                $options['query']=$params;
            }
            if ($headers) {
                $options['headers']=$headers;
            }
            if ($body) {
                $options[RequestOptions::JSON]=$body;

            }
            $clientResponse = $this->client->post($this->resource, $options);
            if ($clientResponse->getStatusCode() == 200) {
                return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }
        return new Response();
    }
}
