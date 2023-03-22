<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
use GuzzleHttp\Exception\TransferException;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CurlDataProvider
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_DELETE = 'DELETE';
    public const DEFAULT_CONTENT_TYPE = 'application/json';

    /**
     * @var string
     */
    private $_url;

    /**
     * Constructor.
     *
     * @param string $uri
     * @param string $resource Resource name for normal resource
     */
    public function __construct(?string $url = null)
    {
        if (!is_null($url)) {
            $this->setUrl($url);
        }
    }

    public function setUrl(string $url)
    {
        $this->_url = $url;
    }

    public function get(array $params = null, array $headers = null, string $body): Response
    {
        try {
            $curl = curl_init();

            if (!is_null($params)) {
                $this->setUrl($this->_url.'?'.http_build_query($params));
            }

            $this->_initRequest($curl, self::METHOD_GET, $headers, $body);

            $results = curl_exec($curl);
            $response = curl_getinfo($curl);

            return new Response($response['http_code'], $results);
        } catch (TransferException $e) {
            return new Response($response['http_code'], $results);
        }

        return new Response();
    }

    public function post(array $headers = null, string $body): Response
    {
        try {
            $curl = curl_init();

            $this->_initRequest($curl, self::METHOD_POST, $headers, $body);

            $results = curl_exec($curl);
            $response = curl_getinfo($curl);

            return new Response($response['http_code'], $results);
        } catch (TransferException $e) {
            return new Response($response['http_code']);
        }

        return new Response();
    }

    public function delete(array $headers = null): Response
    {
        try {
            $curl = curl_init();

            $this->_initRequest($curl, self::METHOD_DELETE, $headers);

            $results = curl_exec($curl);
            $response = curl_getinfo($curl);

            return new Response($response['http_code'], $results);
        } catch (TransferException $e) {
            return new Response($response['http_code'], $results);
        }

        return new Response();
    }

    private function _initRequest($curl, string $method, array $headers = null, string $body = null)
    {
        $options = [
            CURLOPT_URL => $this->_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [],
            CURLOPT_HTTPHEADER => ['Content-Type: '.self::DEFAULT_CONTENT_TYPE],
        ];

        if (!is_null($body)) {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        if (!is_null($headers)) {
            $options[CURLOPT_HTTPHEADER] = array_merge($options[CURLOPT_HTTPHEADER], $headers);
        }

        curl_setopt_array($curl, $options);
    }
}
