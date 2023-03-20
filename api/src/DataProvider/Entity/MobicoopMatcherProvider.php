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

namespace App\DataProvider\Entity;

use App\Carpool\Entity\MobicoopMatcher\Search;
use App\Carpool\Entity\Proposal;
use App\Carpool\Service\MobicoopMatcherAdapter;
use App\DataProvider\Exception\MobicoopMatcherDataProviderException;
use App\DataProvider\Service\CurlDataProvider;
use Psr\Log\LoggerInterface;

/**
 * Mobicoop Matcher V3 data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherProvider
{
    private const ROUTE_AUTH = '/auth';
    private const ROUTE_MATCH = '/match';

    private $_uri;
    private $_username;
    private $_password;
    private $_token;
    private $_logger;
    private $_mobicoopMatcherAdapter;
    private $_curlDataProvider;

    public function __construct(
        LoggerInterface $logger,
        MobicoopMatcherAdapter $mobicoopMatcherAdapter,
        CurlDataProvider $curlDataProvider,
        string $uri,
        string $username,
        string $password
    ) {
        $this->_uri = $uri;
        $this->_username = $username;
        $this->_password = $password;
        $this->_logger = $logger;
        $this->_mobicoopMatcherAdapter = $mobicoopMatcherAdapter;
        $this->_curlDataProvider = $curlDataProvider;
    }

    public function match(Proposal $searchProposal): Proposal
    {
        $this->_logger->info('GET match for proposal '.$searchProposal->getId());
        $this->_auth();
        $search = $this->_buildSearchRequestBody($this->_mobicoopMatcherAdapter->buildSearchFromProposal($searchProposal));
        $results = $this->_get(self::ROUTE_MATCH, $search);
        $this->_logger->info(json_encode($results));

        $matchings = $this->_mobicoopMatcherAdapter->buildMatchingsFromMatcherResult($searchProposal, $results);
        foreach ($matchings as $matching) {
            // var_dump('proposalOffer : '.$matching->getProposalOffer()->getId());
            // var_dump('proposalRequest : '.$matching->getProposalRequest()->getId());
            var_dump($matching);
        }

        // To Do : add the matchings as matchingOffer or matchingRequest

        return $searchProposal;
    }

    private function _get(string $route, string $body): array
    {
        $this->_curlDataProvider->setUrl($this->_uri.''.$route);

        $headers = ['Authorization: Bearer '.$this->_token];

        $response = $this->_curlDataProvider->get(null, $headers, $body);
        if (200 == $response->getCode()) {
            return json_decode($response->getValue(), true);
        }

        $this->_logger->error(MobicoopMatcherDataProviderException::GET_ERROR.' '.$route);
        $this->_logger->error($response->getCode());
        $this->_logger->error($response->getValue());
        $this->_logger->error('Request Body : '.$body);

        throw new MobicoopMatcherDataProviderException(MobicoopMatcherDataProviderException::GET_ERROR.' '.$route);
    }

    private function _auth()
    {
        $this->_curlDataProvider->setUrl($this->_uri.''.self::ROUTE_AUTH);

        $body = [
            'username' => $this->_username,
            'password' => $this->_password,
        ];
        $response = $this->_curlDataProvider->post(null, json_encode($body));

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $this->_token = $data['access_token'];
        } else {
            $this->_logger->error(MobicoopMatcherDataProviderException::AUTH_ERROR);
            $this->_logger->error($response->getCode());
            $this->_logger->error($response->getValue());

            throw new MobicoopMatcherDataProviderException(MobicoopMatcherDataProviderException::AUTH_ERROR);
        }
    }

    private function _buildSearchRequestBody(Search $search): string
    {
        $search = json_encode($search);

        return $this->_cleanBodyRequestFromNullValues($search);
    }

    private function _cleanBodyRequestFromNullValues(string $body): string
    {
        $array = json_decode($body, true);
        $array = array_filter($array, function ($val) {
            return !is_null($val);
        });

        return json_encode($array);
    }
}
