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
     * @return bool                         The result of the send
     */
    public function postCollection(CarpoolProof $carpoolProof)
    {
        // creation of the dataProvider
        $dataProvider = new DataProvider($this->uri, self::RESSOURCE_POST);
        
        // creation of the headers
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Content-Type' => 'application/json'
        ];
        
        // creation of the journey
        // todo : add the required items depending on the class
        $journey = [
            "journey_id" => $carpoolProof->getId(),
            "operator_class" => $carpoolProof->getType(),
            "passenger" => [
                "identity" => [
                    "email" => $carpoolProof->getPassenger()->getEmail(),
                    "phone" => $carpoolProof->getPassenger()->getTelephone()
                ],
                "start" => [
                    "datetime" => $carpoolProof->getPickUpPassengerDate()->format(DateTime::ISO8601),
                    "lon" => $carpoolProof->getPickUpPassengerAddress()->getLongitude(),
                    "lat" => $carpoolProof->getPickUpPassengerAddress()->getLatitude()
                ],
                "end" => [
                    "datetime" => $carpoolProof->getDropOffPassengerDate()->format(DateTime::ISO8601),
                    "lon" => $carpoolProof->getDropOffPassengerAddress()->getLongitude(),
                    "lat" => $carpoolProof->getDropOffPassengerAddress()->getLatitude()
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
                    "datetime" => $carpoolProof->getStartDriverDate()->format(DateTime::ISO8601),
                    "lon" => $carpoolProof->getOriginDriverAddress()->getLongitude(),
                    "lat" => $carpoolProof->getOriginDriverAddress()->getLatitude()
                ],
                "end" => [
                    "datetime" => $carpoolProof->getEndDriverDate()->format(DateTime::ISO8601),
                    "lon" => $carpoolProof->getDestinationDriverAddress()->getLongitude(),
                    "lat" => $carpoolProof->getDestinationDriverAddress()->getLatitude()
                ],
                "revenue" => $carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice()*100,
                "incentives" => []
            ]
        ];

        // todo : treat the result
        $result = $dataProvider->postCollection($journey, $headers);

        return true;
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
