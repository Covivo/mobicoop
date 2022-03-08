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
    public const DATA_NAME_NOT_VALIDATED_USERS = 'NotValidatedUsers';

    public const DATA_NAME_VALIDATED_USERS_DETAILED = 'ValidatedUsersDetailed';
    public const DATA_NAME_NOT_VALIDATED_USERS_DETAILED = 'NotValidatedUsersDetailed';
    public const DATA_NAME_REGISTRATIONS_DETAILED = 'RegistrationsDetailed';

    public const PREFIX_AUTO_CALL_METHOD = 'build';
    public const SUFFIX_AUTO_CALL_METHOD = 'Request';

    public const DATA_NAMES = [
        self::DATA_NAME_VALIDATED_USERS,
        self::DATA_NAME_NOT_VALIDATED_USERS,
        self::DATA_NAME_REGISTRATIONS_DETAILED,
        self::DATA_NAME_VALIDATED_USERS_DETAILED,
        self::DATA_NAME_NOT_VALIDATED_USERS_DETAILED,
    ];
    public const REQUEST_AGGREG_INTERVALS = [
        'daily' => '1d',
        'monthly' => '1M',
        'yearly' => '1y',
    ];
    public const DEFAULT_DETAILED_AGGREG_INTERVAL = self::REQUEST_AGGREG_INTERVALS['monthly'];

    private const REQUEST_TIMOUT = 30000;
    private const DATE_FORMAT = 'c';
    private const DEFAULT_START_DATE_MODIFICATOR = '-1 year';
    private const DEFAULT_END_DATE_MODIFICATOR = '';

    private const BASE_REQUEST = [
        'size' => 0,
        'query' => ['bool' => ['filters' => []]],
    ];

    private $baseUri;
    private $instance;
    private $username;
    private $password;

    private $dataName;
    private $startDate;
    private $endDate;
    private $request;
    private $requestResponse;

    private $response;
    private $keyType;
    private $aggregationInterval;

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

    public function setStartDate(?\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    public function getStartDate(): ?\DateTime
    {
        if (is_null($this->startDate)) {
            $startDate = new \DateTime('now');
            if (self::DEFAULT_START_DATE_MODIFICATOR !== '') {
                $startDate->modify(self::DEFAULT_START_DATE_MODIFICATOR);
            }

            return $startDate;
        }

        return $this->startDate;
    }

    public function setEndDate(?\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    public function getEndDate(): ?\DateTime
    {
        if (is_null($this->endDate)) {
            $endDate = new \DateTime('now');
            if (self::DEFAULT_END_DATE_MODIFICATOR !== '') {
                $endDate->modify(self::DEFAULT_END_DATE_MODIFICATOR);
            }

            return $endDate;
        }

        return $this->endDate;
    }

    public function setAggregationInterval(?string $aggregationInterval)
    {
        $this->aggregationInterval = self::DEFAULT_DETAILED_AGGREG_INTERVAL;
        if (isset(self::REQUEST_AGGREG_INTERVALS[$aggregationInterval])) {
            $this->aggregationInterval = self::REQUEST_AGGREG_INTERVALS[$aggregationInterval];
        }
    }

    public function getAggregationInterval(): ?string
    {
        if (is_null($this->aggregationInterval)) {
            return self::DEFAULT_DETAILED_AGGREG_INTERVAL;
        }

        return $this->aggregationInterval;
    }

    public function getData(): array
    {
        if (!in_array($this->getDataName(), self::DATA_NAMES)) {
            throw new \LogicException('Unkwnown data name');
        }
        $this->buildRequest();

        $this->sendRequest();

        $this->deserializeDataResponse();

        return $this->response;
    }

    private function buildRequest()
    {
        $this->request = self::BASE_REQUEST;
        $functionName = self::PREFIX_AUTO_CALL_METHOD.$this->getDataName().self::SUFFIX_AUTO_CALL_METHOD;
        if (!is_callable([$this, $functionName])) {
            throw new \LogicException('Unkwnown method to retrieve this data name');
        }

        $this->{$functionName}();

        $this->addFilters();
    }

    private function addFilters()
    {
        $this->request['query']['bool']['filter'][] = [
            'range' => [
                'user_created_date' => [
                    'gte' => $this->getStartDate()->format(self::DATE_FORMAT),
                    'lte' => $this->getEndDate()->format(self::DATE_FORMAT),
                ],
            ],
        ];
    }

    private function buildValidatedUsersRequest()
    {
        $this->request['query'] = [
            'bool' => [
                'filter' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => [
                                'query' => 'Validé',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildNotValidatedUsersRequest()
    {
        $this->request['query'] = [
            'bool' => [
                'filter' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => [
                                'query' => 'Non validé',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildRegistrationsDetailedRequest()
    {
        $this->keyType = 'utc-datetime';

        $this->request['aggs'] = [
            1 => [
                'date_histogram' => [
                    'field' => 'user_created_date',
                    'calendar_interval' => $this->getAggregationInterval(),
                    'time_zone' => 'Europe/Paris',
                    'min_doc_count' => 1,
                ],
            ],
        ];

        $this->request['query'] = [
            'bool' => [
                'must_not' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => 'Désinscrit',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildValidatedUsersDetailedRequest()
    {
        $this->keyType = 'datetime';

        $this->request['aggs'] = [
            1 => [
                'date_histogram' => [
                    'field' => 'user_validated_date',
                    'calendar_interval' => $this->getAggregationInterval(),
                    'time_zone' => 'Europe/Paris',
                    'min_doc_count' => 1,
                ],
            ],
        ];

        $this->request['query'] = [
            'bool' => [
                'must' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => 'Validé',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function buildNotValidatedUsersDetailedRequest()
    {
        $this->keyType = 'datetime';

        $this->request['aggs'] = [
            1 => [
                'date_histogram' => [
                    'field' => 'user_created_date',
                    'calendar_interval' => $this->getAggregationInterval(),
                    'time_zone' => 'Europe/Paris',
                    'min_doc_count' => 1,
                ],
            ],
        ];

        $this->request['query'] = [
            'bool' => [
                'must' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => 'Non validé',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function sendRequest()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->baseUri.$this->instance.'*/_search?rest_total_hits_as_int=true&ignore_unavailable=true&ignore_throttled=true&timeout='.self::REQUEST_TIMOUT.'ms',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($this->request),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->username.':'.$this->password,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
        ]);

        $this->requestResponse = curl_exec($curl);

        curl_close($curl);
    }

    private function deserializeDataResponse()
    {
        $dataResponse = json_decode($this->requestResponse, true);

        $this->response = [];

        $this->response['total'] = 0;
        if (isset($dataResponse['hits'])) {
            $this->response['total'] = $dataResponse['hits']['total'];
        }

        $this->response['data'] = [];
        if (isset($dataResponse['aggregations'])) {
            foreach ($dataResponse['aggregations'] as $collection) {
                $dataCollection = [];
                if (isset($collection['buckets'])) {
                    foreach ($collection['buckets'] as $value) {
                        $dataCollection[] = [
                            'key' => $value['key_as_string'],
                            'keyType' => $this->keyType,
                            'interval' => array_search($this->getAggregationInterval(), self::REQUEST_AGGREG_INTERVALS),
                            'value' => $value['doc_count'],
                            'dataName' => $this->getDataName(),
                        ];
                    }
                    $this->response['data'] = $dataCollection;
                }
            }
        }
    }
}
