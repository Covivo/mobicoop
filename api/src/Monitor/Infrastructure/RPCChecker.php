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

use App\DataProvider\Service\CurlDataProvider;
use App\Monitor\Core\Application\Port\Checker;

class RPCChecker implements Checker
{
    public const RPC_URI_SUFFIX = '/v3/journeys';
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
        var_dump($this->_curlDataProvider->get(['status' => 'ok', 'start' => '2024-02-01T01:04:30.004Z'], $this->_headers));

        return 'ok';
    }
}
