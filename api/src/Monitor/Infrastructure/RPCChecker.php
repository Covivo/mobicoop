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
    public const RPC_URI_SUFFIX = 'v3/journeys';
    public const RPC_PROOF_STATUS = 'ok';
    public const PAST_DAYS = '1';

    public const CHECKED = ['message' => 'OK'];
    public const NOT_CHECKED = ['message' => 'KO'];

    private $_curlDataProvider;
    private $_carpoolProofService;
    private $_rpcUri;
    private $_headers = [];

    private $_lastCarpoolId;
    private $_minDate;

    public function __construct(CurlDataProvider $curlDataProvider, CarpoolProofService $carpoolProofService, string $rpcUri, string $rpcToken)
    {
        $this->_curlDataProvider = $curlDataProvider;
        $this->_carpoolProofService = $carpoolProofService;
        $this->_rpcUri = $rpcUri;
        if ('' !== trim($this->_rpcUri) && '/' !== $this->_rpcUri[strlen($this->_rpcUri) - 1]) {
            $this->_rpcUri .= '/';
        }
        $this->_curlDataProvider->setUrl($this->_rpcUri.self::RPC_URI_SUFFIX);
        $this->_headers = [
            'Authorization: Bearer '.$rpcToken,
            'Content-Type: application/json',
        ];
    }

    public function check(): string
    {
        $params = ['status' => self::RPC_PROOF_STATUS];
        $this->_computeMinDate();
        $params['start'] = $this->_minDate;

        return $this->_determineResult($params);
    }

    private function _determineResult(array $params): string
    {
        if (is_null($params['start']) || '' == trim($this->_rpcUri)) {
            return json_encode(self::CHECKED);
        }

        $return = self::NOT_CHECKED;
        $response = $this->_curlDataProvider->get($params, $this->_headers);

        if (is_string($response->getValue()) && is_countable(json_decode($response->getValue(), true)) && count(json_decode($response->getValue(), true)) > 0) {
            $return = self::CHECKED;
        } else {
            if ($this->_checkOnlyLastProof()) {
                $return = self::CHECKED;
            }
        }

        $return['lastCarpoolProofId'] = $this->_lastCarpoolId;
        $return['minDate'] = $this->_minDate;

        return json_encode($return);
    }

    private function _checkOnlyLastProof(): bool
    {
        $uri = $this->_rpcUri.self::RPC_URI_SUFFIX."/Mobicoop_{$this->_lastCarpoolId}";
        $this->_curlDataProvider->setUrl($uri);
        $response = $this->_curlDataProvider->get(null, $this->_headers);

        if (is_string($response->getValue())
            && isset(json_decode($response->getValue(), true)['status'])
            && self::RPC_PROOF_STATUS == json_decode($response->getValue(), true)['status']
        ) {
            return true;
        }

        return false;
    }

    private function _computeMinDate()
    {
        $lastCarpoolProof = $this->_carpoolProofService->getLastCarpoolProof('-'.self::PAST_DAYS.' day');
        if (is_null($lastCarpoolProof)) {
            return;
        }
        $this->_lastCarpoolId = $lastCarpoolProof->getId();

        $minDate = $lastCarpoolProof->getCreatedDate();

        $minDate->setTime(0, 0);

        $this->_minDate = $minDate->format('Y-m-d\TH:i:s\Z');
    }
}
