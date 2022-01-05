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

namespace App\Stats\Admin\Service;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class DataManager
{
    public const DATA_NAME_VALIDATED_USERS = 'ValidatedUsers';

    public const DATA_NAMES = [
        0 => self::DATA_NAME_VALIDATED_USERS,
    ];

    private const REQUEST_TIMOUT = 30000;

    private $baseUri;
    private $instance;
    private $username;
    private $password;

    private $dataName;
    private $request;

    public function __construct(string $baseUri, string $instance, string $username, string $password)
    {
        $this->baseUri = $baseUri;
        $this->instance = $instance;
        $this->username = $username;
        $this->password = $password;
    }

    public function setDataName(string $dataName)
    {
        $this->dataName = $dataName;
    }

    public function getDataName(): string
    {
        return $this->dataName;
    }

    public function getData()
    {
        if (!in_array($this->getDataName(), self::DATA_NAMES)) {
            throw new \LogicException('Unkwnown data name');
        }

        $functionName = 'build'.$this->getDataName().'Request';
        if (!is_callable([$this, $functionName])) {
            throw new \LogicException('Unkwnown method to retrieve this data name');
        }

        $this->{$functionName}();

        echo $this->sendRequest();
    }

    private function buildValidatedUsersRequest()
    {
        $this->request = [
            'size' => 0,
            'query' => [
                'bool' => [
                    'filter' => [
                        0 => [
                            'match_phrase' => [
                                'user_status_label' => [
                                    'query' => 'Validé',
                                ],
                            ],
                        ],
                        1 => [
                            'match_phrase' => [
                                'user_status_label' => [
                                    'query' => 'Validé',
                                ],
                            ],
                        ],
                        2 => [
                            'range' => [
                                'user_created_date' => [
                                    'gte' => '2017-01-05T15:11:00.528Z',
                                    'lte' => '2022-01-05T15:11:00.528Z',
                                    'format' => 'strict_date_optional_time',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function sendRequest(): string
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUri.$this->instance.'*/_search?rest_total_hits_as_int=true&ignore_unavailable=true&ignore_throttled=true&timeout='.self::REQUEST_TIMOUT.'ms',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->request),
            CURLOPT_USERPWD => $this->username.':'.$this->password,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}
