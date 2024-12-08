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

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\DataProvider\Service\CurlDataProvider;
use App\DataProvider\Service\RPCv3\HTTPHeaders;
use App\Monitor\Core\Application\Port\Checker;
use App\OAuth\Service\Manager\TokenManager;

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
    private $_rpcPrefix;

    private $_minDate;

    /**
     * @var null|CarpoolProof
     */
    private $_lastCarpool;

    /**
     * @var TokenManager
     */
    private $_tokenManager;

    public function __construct(
        CurlDataProvider $curlDataProvider,
        CarpoolProofService $carpoolProofService,
        TokenManager $tokenManager,
        string $rpcUri,
        string $rpcPrefix
    ) {
        $this->_curlDataProvider = $curlDataProvider;
        $this->_carpoolProofService = $carpoolProofService;
        $this->_tokenManager = $tokenManager;

        $this->_rpcUri = $rpcUri;
        if ('' !== trim($this->_rpcUri) && '/' !== $this->_rpcUri[strlen($this->_rpcUri) - 1]) {
            $this->_rpcUri .= '/';
        }
        $this->_rpcPrefix = $rpcPrefix;
        $this->_curlDataProvider->setUrl($this->_rpcUri.self::RPC_URI_SUFFIX);
    }

    /**
     * @return false|string
     */
    public function check()
    {
        $params = ['status' => self::RPC_PROOF_STATUS];
        $this->_computeMinDate();
        $params['start'] = $this->_minDate;

        return $this->_determineResult($params);
    }

    /**
     * @return false|string
     */
    private function _determineResult(array $params)
    {
        if (is_null($params['start']) || '' == trim($this->_rpcUri)) {
            return json_encode(self::CHECKED);
        }

        $return = self::NOT_CHECKED;

        $OAuthToken = $this->_tokenManager->getOAuthToken(CarpoolProofGouvProvider::SERVICE_DEFINITION);

        if (is_null($OAuthToken)) {
            return false;
        }

        $response = $this->_curlDataProvider->get($params, HTTPHeaders::getHeaders($OAuthToken->getToken()));

        if (is_string($response->getValue()) && is_countable(json_decode($response->getValue(), true)) && count(json_decode($response->getValue(), true)) > 0) {
            $return = self::CHECKED;
        } else {
            if ($this->_checkOnlyLastProof()) {
                $return = self::CHECKED;
            }
        }

        $return['lastCarpoolProofId'] = $this->_lastCarpool->getId();
        $return['minDate'] = $this->_minDate;

        return json_encode($return);
    }

    private function _checkProofTooOld(): bool
    {
        $yesterday = new \DateTime('now');
        $yesterday->modify('-'.self::PAST_DAYS.' day');
        $yesterday->setTime(0, 0);

        if ($this->_lastCarpool->getCreatedDate() < $yesterday) {
            return true;
        }

        return false;
    }

    private function _checkOnlyLastProof(): bool
    {
        if ($this->_checkProofTooOld()) {
            return false;
        }

        $uri = $this->_rpcUri.self::RPC_URI_SUFFIX."/{$this->_rpcPrefix}{$this->_lastCarpool->getId()}";
        $this->_curlDataProvider->setUrl($uri);

        $OAuthToken = $this->_tokenManager->getOAuthToken(CarpoolProofGouvProvider::SERVICE_DEFINITION);

        if (is_null($OAuthToken)) {
            return false;
        }

        $response = $this->_curlDataProvider->get(null, HTTPHeaders::getHeaders($OAuthToken->getToken()));

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
        $this->_lastCarpool = $this->_carpoolProofService->getLastCarpoolProof('-'.self::PAST_DAYS.' day');
        if (is_null($this->_lastCarpool)) {
            return;
        }

        $minDate = $this->_lastCarpool->getCreatedDate();

        $minDate->setTime(0, 0);

        $this->_minDate = $minDate->format('Y-m-d\TH:i:s\Z');
    }
}
