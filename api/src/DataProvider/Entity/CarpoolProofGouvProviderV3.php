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
use App\DataProvider\Service\RPCv3\Tools;
use Psr\Log\LoggerInterface;

/**
 * Beta gouv carpool proof management service for API v3 effective on June 12, 2023.
 */
class CarpoolProofGouvProviderV3 extends CarpoolProofGouvProvider implements ProviderInterface
{
    public const RESSOURCE_POST = 'v3/journeys';
    public const RESSOURCE_GET_ITEM = 'v3/journeys/';

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
                    'driving_licence' => $this->_tools->getDrivingLicenceNumber(Tools::DRIVER),
                    'operator_user_id' => $this->_tools->getOperatorUserId(Tools::DRIVER),
                    'over_18' => $this->_tools->getOver18(Tools::DRIVER),
                ],
                'revenue' => (int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0),
            ],
        ];
    }
}
