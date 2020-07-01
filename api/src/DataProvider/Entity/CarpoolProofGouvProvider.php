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
 **************************/

namespace App\DataProvider\Entity;

use App\Carpool\Entity\CarpoolProof;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use DateTime;

/**
 * Beta gouv carpool proof management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
class CarpoolProofGouvProvider implements ProviderInterface
{
    const RESSOURCE_POST = "v2/journeys";
    const ISO6801 = 'Y-m-d\TH:i:s\Z';

    // temporary : proof type fixed to 'A', need to be removed when C types will be implemented
    const PROOF_TYPE = 'A';

    private $uri;
    private $token;

    public function __construct(string $uri, string $token)
    {
        $this->uri = $uri;
        $this->token = $token;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Send a carpool proof
     *
     * @param CarpoolProof $carpoolProof    The carpool proof to send
     * @param string|null $prefix           A prefix for the journey id
     * @return Response                     The result of the send
     */
    public function postCollection(CarpoolProof $carpoolProof, ?string $prefix = null)
    {
        // creation of the dataProvider
        $dataProvider = new DataProvider($this->uri, self::RESSOURCE_POST);
        
        // creation of the headers
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ];
        
        // creation of the journey
        // note : the casts are mandatory as the register checks for types
        // todo : add the required items depending on the class
        $journey = [
            "journey_id" => (string)((!is_null($prefix) ? $prefix : "") . (string)$carpoolProof->getId()),
            // TODO : implement other types, for now we use the A type
            // "operator_class" => $carpoolProof->getType(),
            "operator_class" => self::PROOF_TYPE,
            "passenger" => [
                "identity" => [
                    "email" => $carpoolProof->getPassenger()->getEmail(),
                    "phone" => $carpoolProof->getPassenger()->getTelephone()
                ],
                "start" => [
                    "datetime" => $carpoolProof->getPickUpPassengerDate()->format(self::ISO6801),
                    "lon" => (float)$carpoolProof->getPickUpPassengerAddress()->getLongitude(),
                    "lat" => (float)$carpoolProof->getPickUpPassengerAddress()->getLatitude()
                ],
                "end" => [
                    "datetime" => $carpoolProof->getDropOffPassengerDate()->format(self::ISO6801),
                    "lon" => (float)$carpoolProof->getDropOffPassengerAddress()->getLongitude(),
                    "lat" => (float)$carpoolProof->getDropOffPassengerAddress()->getLatitude()
                ],
                "seats" => $carpoolProof->getAsk()->getCriteria()->getSeatsPassenger(),
                "contribution" => $carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice()*100,
                "incentives" => []
            ],
            "driver" => [
                "identity" => [
                    "email" => $carpoolProof->getDriver()->getEmail(),
                    "phone" => $carpoolProof->getDriver()->getTelephone()
                ],
                "start" => [
                    "datetime" => $carpoolProof->getStartDriverDate()->format(self::ISO6801),
                    "lon" => (float)$carpoolProof->getOriginDriverAddress()->getLongitude(),
                    "lat" => (float)$carpoolProof->getOriginDriverAddress()->getLatitude()
                ],
                "end" => [
                    "datetime" => $carpoolProof->getEndDriverDate()->format(self::ISO6801),
                    "lon" => (float)$carpoolProof->getDestinationDriverAddress()->getLongitude(),
                    "lat" => (float)$carpoolProof->getDestinationDriverAddress()->getLatitude()
                ],
                "revenue" => $carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice()*100,
                "incentives" => []
            ]
        ];

        return $dataProvider->postCollection($journey, $headers);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function getItem(string $class, string $apikey, array $params)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function deserialize(string $class, array $data)
    {
        $this->logger->info("BetaGouv API return");
    }
}
