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

    private $uri;
    private $token;
    private $prefix;

    public function __construct(string $uri, string $token, ?string $prefix = null)
    {
        $this->uri = $uri;
        $this->token = $token;
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Send a carpool proof
     *
     * @param CarpoolProof $carpoolProof    The carpool proof to send
     * @return Response                     The result of the send
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
        $over18 = null;
        if (!is_null($carpoolProof->getPassenger()->getBirthDate())) {
            $over18 = $carpoolProof->getPassenger()->getBirthDate()->diff(new DateTime('now'))->y>=18;
        }

        // note : the casts are mandatory as the register checks for types
        $journey = [
            "journey_id" => (string)((!is_null($this->prefix) ? $this->prefix : "") . (string)$carpoolProof->getId()),
            "operator_class" => $carpoolProof->getType(),
            "passenger" => [
                "identity" => [
                    "email" => $carpoolProof->getPassenger()->getEmail(),
                    "phone" => $carpoolProof->getPassenger()->getTelephone(),
                    "over_18" => $over18
                ],
                "start" => [
                    "datetime" => $carpoolProof->getPickUpPassengerDate()->format(self::ISO6801)
                ],
                "end" => [
                    "datetime" => $carpoolProof->getDropOffPassengerDate()->format(self::ISO6801)
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
                    "datetime" => $carpoolProof->getStartDriverDate()->format(self::ISO6801)
                ],
                "end" => [
                    "datetime" => $carpoolProof->getEndDriverDate()->format(self::ISO6801)
                ],
                "revenue" => $carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice()*100,
                "incentives" => []
            ]
        ];
        // additional properties
        if (!is_null($carpoolProof->getPickUpPassengerAddress())) {
            $journey["passenger"]["start"]["lon"] = (float)$carpoolProof->getPickUpPassengerAddress()->getLongitude();
            $journey["passenger"]["start"]["lat"] = (float)$carpoolProof->getPickUpPassengerAddress()->getLatitude();
        }
        if (!is_null($carpoolProof->getDropOffPassengerAddress())) {
            $journey["passenger"]["end"]["lon"] = (float)$carpoolProof->getDropOffPassengerAddress()->getLongitude();
            $journey["passenger"]["end"]["lat"] = (float)$carpoolProof->getDropOffPassengerAddress()->getLatitude();
        }
        
        // Passenger or driver start and end need to be filled with lat/lon to be valid
        // In organized journey, we don't have pickup or dropoff for passengers. We use it's origin/destination
        if (($carpoolProof->getType() == CarpoolProof::TYPE_LOW || $carpoolProof->getType() == CarpoolProof::TYPE_MID) &&
            !isset($journey["passenger"]["start"]["lon"]) && !isset($journey["passenger"]["end"]["lon"])
        ) {
            if ($carpoolProof->getAsk()->getMatching()->getProposalRequest()->getUser()->getId() == $carpoolProof->getPassenger()->getId()) {
                $passengerWaypoints = $carpoolProof->getAsk()->getMatching()->getProposalRequest()->getWaypoints();
            } elseif ($carpoolProof->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() == $carpoolProof->getPassenger()->getId()) {
                $passengerWaypoints = $carpoolProof->getAsk()->getMatching()->getProposalOffer()->getWaypoints();
            }
            $journey["passenger"]["start"]["lon"] = (float)$passengerWaypoints[0]->getAddress()->getLongitude();
            $journey["passenger"]["start"]["lat"] = (float)$passengerWaypoints[0]->getAddress()->getLatitude();
            $journey["passenger"]["end"]["lon"] = (float)$passengerWaypoints[count($passengerWaypoints)-1]->getAddress()->getLongitude();
            $journey["passenger"]["end"]["lat"] = (float)$passengerWaypoints[count($passengerWaypoints)-1]->getAddress()->getLatitude();
        }
        
        if (!is_null($carpoolProof->getOriginDriverAddress())) {
            $journey["driver"]["start"]["lon"] = (float)$carpoolProof->getOriginDriverAddress()->getLongitude();
            $journey["driver"]["start"]["lat"] = (float)$carpoolProof->getOriginDriverAddress()->getLatitude();
        }
        if (!is_null($carpoolProof->getDestinationDriverAddress())) {
            $journey["driver"]["end"]["lon"] = (float)$carpoolProof->getDestinationDriverAddress()->getLongitude();
            $journey["driver"]["end"]["lat"] = (float)$carpoolProof->getDestinationDriverAddress()->getLatitude();
        }
        
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
