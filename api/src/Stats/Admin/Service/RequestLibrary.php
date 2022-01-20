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
class RequestLibrary
{
    public const USER_STATUS_LABEL_UNREGISTERED = 'Désinscrit';
    public const USER_STATUS_LABEL_VALIDATED = 'Validé';
    public const USER_STATUS_LABEL_NOT_VALIDATED = 'Non validé';
    public const USER_STATUS_LABEL_INACTIVE = 'Inactif';

    private $period;
    private $referenceField;
    private $request;

    public function setPeriod(?string $period)
    {
        $this->period = $period;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setReferenceField(?string $referenceField)
    {
        $this->referenceField = $referenceField;
    }

    public function getReferenceField(): ?string
    {
        return $this->referenceField;
    }

    public function setRequest(?array $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ?array
    {
        return $this->request;
    }

    public function buildValidatedUsersRequest()
    {
        $this->request['query'] = [
            'bool' => [
                'filter' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => [
                                'query' => self::USER_STATUS_LABEL_VALIDATED,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function buildRegistrationsListRequest()
    {
        $this->addAggregationParams();

        $this->request['query'] = [
            'bool' => [
                'must_not' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => self::USER_STATUS_LABEL_UNREGISTERED,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function buildValidatedUsersListRequest()
    {
        $this->addAggregationParams();

        $this->buildValidatedUsersRequest();
    }

    public function buildNotValidatedUsersListRequest()
    {
        $this->addAggregationParams();

        $this->request['query'] = [
            'bool' => [
                'must' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => self::USER_STATUS_LABEL_NOT_VALIDATED,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function buildUnregisteredUsersListRequest()
    {
        $this->addAggregationParams();

        $this->request['query'] = [
            'bool' => [
                'must' => [
                    [
                        'match_phrase' => [
                            'user_status_label' => self::USER_STATUS_LABEL_UNREGISTERED,
                        ],
                    ],
                ],
            ],
        ];
    }

    private function addAggregationParams()
    {
        $this->request['aggs'] = [
            1 => [
                'date_histogram' => [
                    'field' => $this->getReferenceField(),
                    'calendar_interval' => $this->getPeriod(),
                    'time_zone' => 'Europe/Paris',
                    'min_doc_count' => 1,
                ],
            ],
        ];
    }
}
