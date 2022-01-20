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
    public const DATA_NAME_REGISTRATIONS_LIST = 'RegistrationsList';
    public const DATA_NAME_VALIDATED_USERS_LIST = 'ValidatedUsersList';
    public const DATA_NAME_NOT_VALIDATED_USERS_LIST = 'NotValidatedUsersList';
    public const DATA_NAME_UNREGISTERED_USERS_LIST = 'UnregisteredUsersList';
    public const DATA_NAME_USERS_STATS_LIST = 'UsersStatsList';

    public const DATA_NAMES = [
        self::DATA_NAME_VALIDATED_USERS => ['childrenMethods' => [], 'keyType' => ''],
        self::DATA_NAME_REGISTRATIONS_LIST => ['childrenMethods' => [], 'keyType' => 'utc-datetime'],
        self::DATA_NAME_VALIDATED_USERS_LIST => ['childrenMethods' => [], 'keyType' => 'utc-datetime'],
        self::DATA_NAME_NOT_VALIDATED_USERS_LIST => ['childrenMethods' => [], 'keyType' => 'utc-datetime'],
        self::DATA_NAME_UNREGISTERED_USERS_LIST => ['childrenMethods' => [], 'keyType' => 'utc-datetime'],
        self::DATA_NAME_USERS_STATS_LIST => ['childrenMethods' => [self::DATA_NAME_VALIDATED_USERS_LIST, self::DATA_NAME_NOT_VALIDATED_USERS_LIST, self::DATA_NAME_UNREGISTERED_USERS_LIST], 'keyType' => ''],
    ];

    public const PREFIX_AUTO_CALL_METHOD = 'build';
    public const SUFFIX_AUTO_CALL_METHOD = 'Request';

    private const REQUEST_TIMOUT = 30000;
    private const DATE_FORMAT = 'c';
    private const DEFAULT_START_DATE_MODIFICATOR = '-1 year';
    private const DEFAULT_END_DATE_MODIFICATOR = '';
    private const DEFAULT_PERIOD = '1M';
    private const DEFAULT_REFERENCE_FIELD = 'user_created_date';

    private const BASE_REQUEST = [
        'size' => 0,
        'query' => ['bool' => ['filters' => []]],
    ];

    private $baseUri;
    private $instance;
    private $username;
    private $password;
    private $requestLibrary;

    private $dataName;
    private $startDate;
    private $endDate;
    private $period;
    private $referenceField;
    private $request;
    private $requestResponse;

    private $lastRequestResponse;
    private $response;
    private $keyType;

    public function __construct(string $baseUri, string $instance, string $username, string $password)
    {
        $this->baseUri = $baseUri;
        $this->instance = $instance;
        $this->username = $username;
        $this->password = $password;
        $this->requestLibrary = new RequestLibrary();
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

    public function setPeriod(?string $period)
    {
        $this->period = $period;
    }

    public function getPeriod(): ?string
    {
        if (is_null($this->period)) {
            return self::DEFAULT_PERIOD;
        }

        return $this->period;
    }

    public function setReferenceField(?string $referenceField)
    {
        $this->referenceField = $referenceField;
    }

    public function getReferenceField(): ?string
    {
        if (is_null($this->referenceField)) {
            return self::DEFAULT_REFERENCE_FIELD;
        }

        return $this->referenceField;
    }

    public function getData(): array
    {
        if (!isset(self::DATA_NAMES[$this->getDataName()])) {
            throw new \LogicException('Unkwnown data name {'.$this->getDataName().'}');
        }

        if (count(self::DATA_NAMES[$this->getDataName()]['childrenMethods']) > 0) {
            $this->treatMultipleRequests();
        } else {
            $this->treatSimpleRequest();
        }

        return $this->response;
    }

    private function treatSimpleRequest()
    {
        $this->buildRequest();

        $this->sendRequest();

        $this->deserializeDataResponse();

        $this->response[] = $this->lastRequestResponse;
    }

    private function treatMultipleRequests()
    {
        foreach (self::DATA_NAMES[$this->getDataName()]['childrenMethods'] as $childrenMethods) {
            $this->setDataName($childrenMethods);
            $this->treatSimpleRequest();
        }
    }

    private function buildRequest()
    {
        $this->request = self::BASE_REQUEST;

        $functionName = self::PREFIX_AUTO_CALL_METHOD.$this->getDataName().self::SUFFIX_AUTO_CALL_METHOD;
        if (!is_callable([$this->requestLibrary, $functionName])) {
            throw new \LogicException('Unkwnown method to retrieve this data name {'.$this->getDataName().'}');
        }

        $this->requestLibrary->setPeriod($this->getPeriod());
        $this->requestLibrary->setReferenceField($this->getReferenceField());
        $this->requestLibrary->setRequest($this->request);
        $this->requestLibrary->{$functionName}();
        $this->request = $this->requestLibrary->getRequest();
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

        $this->lastRequestResponse = [];

        $this->lastRequestResponse['total'] = 0;
        if (isset($dataResponse['hits'])) {
            $this->lastRequestResponse['total'] = $dataResponse['hits']['total'];
        }

        $this->lastRequestResponse['data'] = [];
        if (isset($dataResponse['aggregations'])) {
            foreach ($dataResponse['aggregations'] as $collection) {
                $dataCollection = [];
                if (isset($collection['buckets'])) {
                    foreach ($collection['buckets'] as $value) {
                        $dataCollection[] = [
                            'dataName' => $this->getDataName(),
                            'key' => $value['key_as_string'],
                            'keyType' => self::DATA_NAMES[$this->getDataName()]['keyType'],
                            'value' => $value['doc_count'],
                        ];
                    }
                    $this->lastRequestResponse['data'] = $dataCollection;
                }
            }
        }
    }
}
