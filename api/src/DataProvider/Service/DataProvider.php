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
 */

namespace App\DataProvider\Service;

use App\DataProvider\Entity\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise;
use GuzzleHttp\RequestOptions;

/**
 * Data provider service.
 * Uses an API to retrieve/send data.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class DataProvider
{
    public const BODY_TYPE_JSON = RequestOptions::JSON;
    public const BODY_TYPE_FORM_PARAMS = RequestOptions::FORM_PARAMS;

    /**
     * @var Client
     */
    private $client;
    private $resource;

    /**
     * Constructor.
     *
     * @param string $uri
     * @param string $resource Resource name for normal resource
     */
    public function __construct(?string $uri = null, ?string $resource = null)
    {
        if (!is_null($uri)) {
            $this->setUri($uri);
        }
        $this->resource = $resource;
    }

    public function setUri(string $uri)
    {
        $this->client = new Client([
            'base_uri' => $uri,
        ]);
    }

    public function setResource(?string $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get item operation.
     *
     * @param int $id The id of the item
     *
     * @return Response the response of the operation
     */
    public function getItem(array $params, array $headers = null): Response
    {
        try {
            $clientResponse = $this->client->get($this->resource.'?'.http_build_query($params), ['headers' => $headers]);

            return new Response($clientResponse->getStatusCode(), $clientResponse->getBody()->getContents());
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }

        return new Response();
    }

    /**
     * Get collection operation.
     *
     * @param null|mixed $params  An array or string of parameters
     * @param null|array $headers An array of headers
     *
     * @return Response the response of the operation
     */
    public function getCollection($params = null, $headers = null): Response
    {
        try {
            $clientResponse = $this->client->get($this->resource, ['query' => $params, 'headers' => $headers]);
            if (200 == $clientResponse->getStatusCode()) {
                return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode(), $e->getMessage() ? $e->getMessage() : null);
        }

        return new Response();
    }

    /**
     * Get async collection operation.
     *
     * @param null|mixed $params An array of parameters
     *
     * @return Response the response of the operation
     */
    public function getAsyncCollection($params = null): Response
    {
        $promises = [];
        foreach ($params as $key => $resource) {
            $promises[$key] = $this->client->getAsync($this->resource, ['query' => $resource]);
        }

        try {
            $results = Promise\unwrap($promises);
            $bodies = [];
            foreach ($results as $key => $result) {
                $bodies[$key] = $result->getBody();
            }

            return new Response(200, $bodies);
        } catch (ConnectException $e) {
            return new Response($e->getCode());
        }

        return new Response();
    }

    /**
     * Get collection operation.
     *
     * @param null|mixed $body     The body
     * @param null|array $headers  An array of headers
     * @param null|mixed $params   An array or string of parameters
     * @param null|mixed $bodyType
     *
     * @return Response the response of the operation
     */
    public function postCollection($body = null, $headers = null, $params = null, $bodyType = null, array $auth = null): Response
    {
        try {
            $options = [];
            if ($params) {
                $options['query'] = $params;
            }
            if ($headers) {
                $options['headers'] = $headers;
            }
            if ($body) {
                if (is_null($bodyType)) {
                    switch ($bodyType) {
                        case self::BODY_TYPE_JSON: $options[self::BODY_TYPE_JSON] = $body;

                            break;

                        case self::BODY_TYPE_FORM_PARAMS: $options[self::BODY_TYPE_FORM_PARAMS] = $body;

                            break;

                        default: $options[self::BODY_TYPE_JSON] = $body;
                    }
                } else {
                    $options[$bodyType] = $body;
                }
            }
            if (!is_null($auth)) {
                $options[RequestOptions::AUTH] = $auth;
            }

            // var_dump($body);
            // var_dump($options);

            // exit;

            $clientResponse = $this->client->post($this->resource, $options);

            switch ($clientResponse->getStatusCode()) {
                case 200:
                case 201:
                case 204:
                    return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            // var_dump($e->getMessage());die;
            return new Response($e->getCode(), $e->getMessage());
        }

        return new Response();
    }

    /**
     * Put item operation.
     *
     * @param null|mixed $body     The body
     * @param null|array $headers  An array of headers
     * @param null|mixed $params   An array or string of parameters
     * @param null|mixed $bodyType
     *
     * @return Response the response of the operation
     */
    public function putItem($body = null, $headers = null, $params = null, $bodyType = null): Response
    {
        try {
            $options = [];
            if ($params) {
                $options['query'] = $params;
            }
            if ($headers) {
                $options['headers'] = $headers;
            }
            if ($body) {
                if (is_null($bodyType)) {
                    switch ($bodyType) {
                        case self::BODY_TYPE_JSON: $options[self::BODY_TYPE_JSON] = $body;

                            break;

                        case self::BODY_TYPE_FORM_PARAMS: $options[self::BODY_TYPE_FORM_PARAMS] = $body;

                            break;

                        default: $options[self::BODY_TYPE_JSON] = $body;
                    }
                } else {
                    $options[$bodyType] = $body;
                }
            }

            // echo json_encode($body);
            // var_dump($options);
            // die;

            $clientResponse = $this->client->put($this->resource, $options);

            switch ($clientResponse->getStatusCode()) {
                case 200:
                case 201:
                    return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode());
        }

        return new Response();
    }

    /**
     * Patch item operation.
     *
     * @param null|mixed $body     The body
     * @param null|array $headers  An array of headers
     * @param null|mixed $params   An array or string of parameters
     * @param null|mixed $bodyType
     *
     * @return Response the response of the operation
     */
    public function patchItem($body = null, $headers = null, $params = null, $bodyType = null): Response
    {
        try {
            $options = [];
            if ($params) {
                $options['query'] = $params;
            }
            if ($headers) {
                $options['headers'] = $headers;
            }
            if ($body) {
                if (is_null($bodyType)) {
                    switch ($bodyType) {
                        case self::BODY_TYPE_JSON: $options[self::BODY_TYPE_JSON] = $body;

                            break;

                        case self::BODY_TYPE_FORM_PARAMS: $options[self::BODY_TYPE_FORM_PARAMS] = $body;

                            break;

                        default: $options[self::BODY_TYPE_JSON] = $body;
                    }
                } else {
                    $options[$bodyType] = $body;
                }
            }

            $clientResponse = $this->client->patch($this->resource, $options);

            switch ($clientResponse->getStatusCode()) {
                case 200:
                case 201:
                case 204:
                    return new Response($clientResponse->getStatusCode(), $clientResponse->getBody());
            }
        } catch (TransferException $e) {
            return new Response($e->getCode(), $e->getMessage());
        }

        return new Response();
    }
}
