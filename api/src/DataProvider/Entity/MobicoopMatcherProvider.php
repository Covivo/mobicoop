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
use App\Carpool\Service\MobicoopMatcher\MobicoopMatcherAdapter;
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
    private const ROUTE_POST = '/ad';
    private const ROUTE_DELETE = '/ad';

    private const HTTP_CODE_OK = 200;
    private const HTTP_CODE_NO_CONTENT = 204;

    private $_uri;
    private $_headers;
    private $_logger;
    private $_mobicoopMatcherAdapter;
    private $_curlDataProvider;

    public function __construct(
        LoggerInterface $logger,
        MobicoopMatcherAdapter $mobicoopMatcherAdapter,
        CurlDataProvider $curlDataProvider,
        string $uri,
        string $apiKey
    ) {
        $this->_uri = $uri;
        $this->_headers = ['X-Api-Key: '.$apiKey];
        $this->_logger = $logger;
        $this->_mobicoopMatcherAdapter = $mobicoopMatcherAdapter;
        $this->_curlDataProvider = $curlDataProvider;
    }

    public function match(Proposal $searchProposal): Proposal
    {
        $this->_logger->info('----- GET match for proposal '.$searchProposal->getId());
        $search = $this->_buildSearchRequestBody($this->_mobicoopMatcherAdapter->buildSearchFromProposal($searchProposal));
        $results = $this->_get(self::ROUTE_MATCH, $search);
        $this->_logger->info(json_encode($results));

        $this->_feedSearchProposalWithMatchings($searchProposal, $results);

        return $searchProposal;
    }

    public function post(Proposal $searchProposal): Proposal
    {
        $this->_logger->info('----- POST proposal '.$searchProposal->getId());
        $ad = $this->_buildSearchRequestBody($this->_mobicoopMatcherAdapter->buildAdFromProposal($searchProposal));
        $results = $this->_post(self::ROUTE_POST, $ad);
        $this->_logger->info(json_encode($results));

        $this->_feedSearchProposalWithMatchings($searchProposal, $results);

        return $searchProposal;
    }

    public function delete(int $proposalId)
    {
        $this->_delete(self::ROUTE_DELETE, $proposalId);
    }

    private function _feedSearchProposalWithMatchings(Proposal $searchProposal, array $results)
    {
        $matchings = $this->_mobicoopMatcherAdapter->buildMatchingsFromMatcherResult($searchProposal, $results);

        // REMOVE THIS PART AFTER DEV
        // foreach ($matchings as $matching) {
        //     $matching->setProposalOffer(null);
        //     $matching->setProposalRequest(null);
        //     var_dump($matching);
        // }

        // exit;
        // END REMOVE THIS PART AFTER DEV

        foreach ($matchings as $matching) {
            if ($matching->getProposalOffer()->getId() == $searchProposal->getId()) {
                $searchProposal->addMatchingRequest($matching);
            } elseif ($matching->getProposalRequest()->getId() == $searchProposal->getId()) {
                $searchProposal->addMatchingOffer($matching);
            }
        }
    }

    private function _get(string $route, string $body): array
    {
        $this->_curlDataProvider->setUrl($this->_uri.''.$route);

        $this->_logger->info('Request :');
        $this->_logger->info($body);
        $response = $this->_curlDataProvider->get(null, $this->_headers, $body);
        if (self::HTTP_CODE_OK == $response->getCode()) {
            return json_decode($response->getValue(), true);
        }

        $this->_logger->error(MobicoopMatcherDataProviderException::GET_ERROR.' '.$route);
        $this->_logger->error($response->getCode());
        $this->_logger->error(strip_tags($response->getValue()));
        $this->_logger->error('Request Body : '.$body);

        throw new MobicoopMatcherDataProviderException(MobicoopMatcherDataProviderException::GET_ERROR.' '.$route);
    }

    private function _post(string $route, string $body): array
    {
        $this->_curlDataProvider->setUrl($this->_uri.''.$route);

        $this->_logger->info('Request :');
        $this->_logger->info($body);
        $response = $this->_curlDataProvider->post($this->_headers, $body);
        if (self::HTTP_CODE_OK == $response->getCode()) {
            return json_decode($response->getValue(), true);
        }

        $this->_logger->error(MobicoopMatcherDataProviderException::POST_ERROR.' '.$route);
        $this->_logger->error($response->getCode());
        $this->_logger->error(strip_tags($response->getValue()));
        $this->_logger->error('Request Body : '.$body);

        throw new MobicoopMatcherDataProviderException(MobicoopMatcherDataProviderException::POST_ERROR.' '.$route);
    }

    private function _delete(string $route, int $id)
    {
        $this->_curlDataProvider->setUrl($this->_uri.''.$route.'/'.$id);

        $this->_logger->info('Request id :'.$id);
        $response = $this->_curlDataProvider->delete($this->_headers);
        if (self::HTTP_CODE_OK != $response->getCode()) {
            $this->_logger->error(MobicoopMatcherDataProviderException::DELETE_ERROR.' '.$route);
            $this->_logger->error($response->getCode());
            $this->_logger->error(strip_tags($response->getValue()));
            $this->_logger->error('Request Id : '.$id);
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

        if (isset($array['schedule'])) {
            $array['schedule'] = array_filter($array['schedule'], function ($val) {
                return !is_null($val);
            });
        }

        if (isset($array['margin_durations'])) {
            $array['margin_durations'] = array_filter($array['margin_durations'], function ($val) {
                return !is_null($val);
            });
        }

        return json_encode($array);
    }
}
