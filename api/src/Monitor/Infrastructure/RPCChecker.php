<?php

/*
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Monitor\Infrastructure;

use App\DataProvider\Entity\Response;
use App\DataProvider\Service\CurlDataProvider;
use App\Monitor\Core\Application\Port\Checker;

class RPCChecker implements Checker
{
    public const RPC_URI_SUFFIX = '/v3/journeys';
    public const RPC_PROOF_STATUS = 'ok';
    public const PAST_DAYS = '50';

    public const CHECKED = 'OK';
    public const NOT_CHECKED = 'KO';

    private $_curlDataProvider;
    private $_headers = [];

    public function __construct(CurlDataProvider $curlDataProvider, string $rpcUri, string $rpcToken)
    {
        $this->_curlDataProvider = $curlDataProvider;
        $this->_curlDataProvider->setUrl($rpcUri.self::RPC_URI_SUFFIX);
        $this->_headers = [
            'Authorization: Bearer '.$rpcToken,
            'Content-Type: application/json',
        ];
    }

    public function check(): string
    {
        $params = ['status' => self::RPC_PROOF_STATUS];
        $params['start'] = $this->_computeMinDate();

        return $this->_determineResult($this->_curlDataProvider->get($params, $this->_headers));
    }

    private function _determineResult(Response $response): string
    {
        $return = self::NOT_CHECKED;
        if (is_string($response->getValue()) && count(json_decode($response->getValue(), true)) > 0) {
            $return = self::CHECKED;
        }

        return $return;
    }

    private function _computeMinDate(): string
    {
        $minDate = new \DateTime('now');
        $minDate->modify('-'.self::PAST_DAYS.' day');

        return $minDate->format('Y-m-d\\TH:i:s\\Z');
    }
}
