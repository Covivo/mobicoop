<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use App\DataProvider\Service\RPCv3\Tools;
use Psr\Log\LoggerInterface;

/**
 * Beta gouv carpool proof management service for API v3 effective on June 12, 2023.
 */
class CarpoolProofGouvProviderV3 extends CarpoolProofGouvProvider implements ProviderInterface
{
    public const RESSOURCE_POST = 'v3/journeys';
    public const RESSOURCE_GET_ITEM = 'v3/journeys/';
    public const RESOURCE_POLICIES_CEE_IMPORT = 'v3/policies/cee/import';

    public const POLICIES_CEE_IMPORT_LIMIT = 1000;

    public function __construct(Tools $tools, string $uri, string $token, ?string $prefix = null, LoggerInterface $logger, bool $testMode = false)
    {
        parent::__construct($tools, $uri, $token, $prefix, $logger, $testMode);
    }

    public function serializeProof(CarpoolProof $carpoolProof): ?array
    {
        $this->_tools->setCurrentCarpoolProof($carpoolProof);

        // note : the casts are mandatory as the register checks for types
        return [
            'incentive_counterparts' => [],
            'operator_journey_id' => $this->_tools->getOperatorJourneyId(),
            'operator_trip_id' => $this->_tools->computeOperatorTripId($carpoolProof),
            'operator_class' => $carpoolProof->getType(),
            'incentives' => [],
            'start' => $this->_tools->getStartTimeGeopoint(),
            'end' => $this->_tools->getEndTimeGeopoint(),
            'distance' => $this->_tools->getDistance(),
            'passenger' => [
                'identity' => [
                    'identity_key' => $this->_tools->getIdentityKey(Tools::PASSENGER),
                    // 'phone' => $this->_tools->getPhoneNumber(Tools::PASSENGER),
                    'phone_trunc' => $this->_tools->getPhoneTruncNumber(Tools::PASSENGER),
                    'operator_user_id' => $this->_tools->getOperatorUserId(Tools::PASSENGER),
                    'over_18' => $this->_tools->getOver18(Tools::PASSENGER),
                ],
                'contribution' => (int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0),
                'seats' => $carpoolProof->getAsk()->getCriteria()->getSeatsPassenger(),
            ],
            'driver' => [
                'identity' => [
                    'identity_key' => $this->_tools->getIdentityKey(Tools::DRIVER),
                    // 'phone' => $this->_tools->getPhoneNumber(Tools::DRIVER),
                    'phone_trunc' => $this->_tools->getPhoneTruncNumber(Tools::DRIVER),
                    'operator_user_id' => $this->_tools->getOperatorUserId(Tools::DRIVER),
                    'over_18' => $this->_tools->getOver18(Tools::DRIVER),
                ],
                'revenue' => (int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0),
            ],
        ];

        // if (!is_null($this->_tools->getDrivingLicenceNumber(Tools::DRIVER))) {
        //     $serializedProof['driver']['identity']['driving_license'] = $this->_tools->getDrivingLicenceNumber(Tools::DRIVER);
        // }
    }

    public function importProofs(array $carpoolProofs)
    {
        $serializedProof = array_map(function ($proof) {
            return $this->_serializeForCeePolicy($proof);
        }, $carpoolProofs);

        $chunkedProofs = array_chunk($serializedProof, self::POLICIES_CEE_IMPORT_LIMIT);

        $this->logger->info('Processing sending '.count($chunkedProofs).' request(s)');

        foreach ($chunkedProofs as $key => $proofs) {
            // creation of the dataProvider
            $dataProvider = new DataProvider($this->uri, self::RESSOURCE_POST);

            // creation of the headers
            $headers = [
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
            ];

            $result = $dataProvider->postCollection($proofs, $headers);

            if (201 === $result->getCode()) {
                $this->logger->info('The processing of the request '.($key + 1).' was successful');
            } else {
                $this->logger->info('There was a problem processing the request '.($key + 1));
            }
        }

        $this->logger->info('Request processing is complete');
    }

    private function _serializeForCeePolicy(CarpoolProof $carpoolProof): array
    {
        $this->_tools->setCurrentCarpoolProof($carpoolProof);

        return [
            'journey_type' => $this->_tools->getProofType(),
            'phone_trunc' => $this->_tools->getPhoneTruncNumber(Tools::DRIVER),
            'last_name_trunc' => $this->_tools->getFamilyNameTrunc(Tools::DRIVER),
            'datetime' => $this->_tools->getCommitmentDate(),
        ];
    }
}
