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
use App\Carpool\Entity\Criteria;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;
use Psr\Log\LoggerInterface;

/**
 * Beta gouv carpool proof management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class CarpoolProofGouvProvider implements ProviderInterface
{
    public const RESSOURCE_POST = 'v2/journeys';
    public const ISO6801 = 'Y-m-d\TH:i:s\Z';
    public const RESSOURCE_GET_ITEM = 'v2/journeys/';

    private $uri;
    private $token;
    private $prefix;
    private $logger;
    private $testMode;

    public function __construct(string $uri, string $token, ?string $prefix = null, LoggerInterface $logger, bool $testMode = false)
    {
        $this->uri = $uri;
        $this->token = $token;
        $this->prefix = $prefix;
        $this->logger = $logger;
        $this->testMode = $testMode;
    }

    /**
     * {@inheritdoc}
     */
    /**
     * Send a carpool proof.
     *
     * @param CarpoolProof $carpoolProof The carpool proof to send
     *
     * @return Response The result of the send
     */
    public function postCollection(CarpoolProof $carpoolProof)
    {
        if (is_null($carpoolProof->getAsk())) {
            return new Response(418);
        }

        // creation of the dataProvider
        $dataProvider = new DataProvider($this->uri, self::RESSOURCE_POST);

        // creation of the headers
        $headers = [
            'Authorization' => 'Bearer '.$this->token,
            'Content-Type' => 'application/json',
        ];

        $journey = $this->serializeProof($carpoolProof);

        if (!is_null($journey)) {
            $this->logger->info('Send Proof #'.$carpoolProof->getId());
            $this->logger->info(json_encode($journey));

            if ($this->testMode) {
                return new Response(200, '');
            }

            return $dataProvider->postCollection($journey, $headers);
        }

        $this->logger->info('Proof #'.$carpoolProof->getId().' ignored');

        return new Response(200, '');
    }

    public function serializeProof(CarpoolProof $carpoolProof): ?array
    {
        // creation of the journey
        $over18 = null;
        if (!is_null($carpoolProof->getPassenger()->getBirthDate())) {
            $over18 = $carpoolProof->getPassenger()->getBirthDate()->diff(new \DateTime('now'))->y >= 18;
        }

        // note : the casts are mandatory as the register checks for types
        $journey = [
            'journey_id' => (string) ((!is_null($this->prefix) ? $this->prefix : '').(string) $carpoolProof->getId()),
            'operator_class' => $carpoolProof->getType(),
            'passenger' => [
                'identity' => [
                    'email' => $carpoolProof->getPassenger()->getEmail(),
                    'phone' => $carpoolProof->getPassenger()->getTelephone(),
                    'over_18' => $over18,
                ],
                'start' => [
                    'lon' => (!is_null($carpoolProof->getPickUpPassengerAddress())) ? (float) $carpoolProof->getPickUpPassengerAddress()->getLongitude() : null,
                    'lat' => (!is_null($carpoolProof->getPickUpPassengerAddress())) ? (float) $carpoolProof->getPickUpPassengerAddress()->getLatitude() : null,
                    'datetime' => (!is_null($carpoolProof->getPickUpPassengerDate())) ? $carpoolProof->getPickUpPassengerDate()->format(self::ISO6801) : null,
                ],
                'end' => [
                    'lon' => (!is_null($carpoolProof->getDropOffPassengerAddress())) ? (float) $carpoolProof->getDropOffPassengerAddress()->getLongitude() : null,
                    'lat' => (!is_null($carpoolProof->getDropOffPassengerAddress())) ? (float) $carpoolProof->getDropOffPassengerAddress()->getLatitude() : null,
                    'datetime' => (!is_null($carpoolProof->getDropOffPassengerDate())) ? $carpoolProof->getDropOffPassengerDate()->format(self::ISO6801) : null,
                ],
                'seats' => $carpoolProof->getAsk()->getCriteria()->getSeatsPassenger(),
                'contribution' => (int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0),
                'incentives' => [],
            ],
            'driver' => [
                'identity' => [
                    'email' => $carpoolProof->getDriver()->getEmail(),
                    'phone' => $carpoolProof->getDriver()->getTelephone(),
                ],
                'start' => [
                    'lon' => (!is_null($carpoolProof->getPickUpDriverAddress())) ? (float) $carpoolProof->getPickUpDriverAddress()->getLongitude() : null,
                    'lat' => (!is_null($carpoolProof->getPickUpDriverAddress())) ? (float) $carpoolProof->getPickUpDriverAddress()->getLatitude() : null,
                    'datetime' => (!is_null($carpoolProof->getPickUpDriverDate())) ? $carpoolProof->getPickUpDriverDate()->format(self::ISO6801) : null,
                ],
                'end' => [
                    'lon' => (!is_null($carpoolProof->getDropOffDriverAddress())) ? (float) $carpoolProof->getDropOffDriverAddress()->getLongitude() : null,
                    'lat' => (!is_null($carpoolProof->getDropOffDriverAddress())) ? (float) $carpoolProof->getDropOffDriverAddress()->getLatitude() : null,
                    'datetime' => (!is_null($carpoolProof->getDropOffDriverDate())) ? $carpoolProof->getDropOffDriverDate()->format(self::ISO6801) : null,
                ],
                'revenue' => (int) round($carpoolProof->getAsk()->getCriteria()->getPassengerComputedRoundedPrice() * 100, 0),
                'incentives' => [],
            ],
        ];
        // additional properties

        // Passenger or driver start and end need to be filled with lat/lon to be valid
        // In organized journey, we don't have pickup or dropoff for passengers. We use it's origin/destination
        if (CarpoolProof::TYPE_LOW == $carpoolProof->getType() || CarpoolProof::TYPE_MID == $carpoolProof->getType()) {
            if (is_null($journey['passenger']['start']['lon']) && is_null($journey['passenger']['start']['lat'])) {
                $matchingWaypoints = $carpoolProof->getAsk()->getMatching()->getWaypoints();
                $passengerWaypoints = [];
                foreach ($matchingWaypoints as $waypoint) {
                    if (2 == $waypoint->getRole()) {
                        $passengerWaypoints[] = $waypoint;
                    }
                }
                $journey['passenger']['start']['lon'] = (float) $passengerWaypoints[0]->getAddress()->getLongitude();
                $journey['passenger']['start']['lat'] = (float) $passengerWaypoints[0]->getAddress()->getLatitude();
                $journey['passenger']['end']['lon'] = (float) $passengerWaypoints[count($passengerWaypoints) - 1]->getAddress()->getLongitude();
                $journey['passenger']['end']['lat'] = (float) $passengerWaypoints[count($passengerWaypoints) - 1]->getAddress()->getLatitude();
            }

            $journey['driver']['start']['lon'] = $journey['passenger']['start']['lon'];
            $journey['driver']['start']['lat'] = $journey['passenger']['start']['lat'];
            $journey['driver']['end']['lon'] = $journey['passenger']['end']['lon'];
            $journey['driver']['end']['lat'] = $journey['passenger']['end']['lat'];

            // In organized, we need to use the driver's date and we search for the ask's criteria time for the passenger
            if (is_null($journey['passenger']['start']['datetime'])) {
                $fromDate = $carpoolProof->getStartDriverDate();
                if (Criteria::FREQUENCY_PUNCTUAL == $carpoolProof->getAsk()->getCriteria()->getFrequency()) {
                    $fromTime = $carpoolProof->getAsk()->getCriteria()->getFromTime();
                } else {
                    // Need to find the right time
                    switch ($fromDate->format('w')) {
                        case 0:
                            if (!$carpoolProof->getAsk()->getCriteria()->isSunCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getSunTime();

                            break;

                        case 1:
                            if (!$carpoolProof->getAsk()->getCriteria()->isMonCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getMonTime();

                            break;

                        case 2:
                            if (!$carpoolProof->getAsk()->getCriteria()->isTueCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getTueTime();

                            break;

                        case 3:
                            if (!$carpoolProof->getAsk()->getCriteria()->isWedCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getWedTime();

                            break;

                        case 4:
                            if (!$carpoolProof->getAsk()->getCriteria()->isThuCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getThuTime();

                            break;

                        case 5:
                            if (!$carpoolProof->getAsk()->getCriteria()->isFriCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getFriTime();

                            break;

                        case 6:
                            if (!$carpoolProof->getAsk()->getCriteria()->isSatCheck()) {
                                return null;
                            }
                            $fromTime = $carpoolProof->getAsk()->getCriteria()->getSatTime();

                            break;
                    }
                }

                // We compute the pickup time
                $pickUpTime = $fromTime->modify('+ '.$carpoolProof->getAsk()->getMatching()->getPickUpDuration().' second');
                $passengerStartDate = clone $fromDate;
                $journey['passenger']['start']['datetime'] = $passengerStartDate->setTime($pickUpTime->format('H'), $pickUpTime->format('i'), $pickUpTime->format('s'))->format(self::ISO6801);

                // We compute the drop off time
                $dropOffTime = $fromTime->modify('+ '.$carpoolProof->getAsk()->getMatching()->getDropOffDuration().' second');
                $passengerEndDate = clone $fromDate;
                $journey['passenger']['end']['datetime'] = $passengerEndDate->setTime($dropOffTime->format('H'), $dropOffTime->format('i'), $dropOffTime->format('s'))->format(self::ISO6801);
            }

            if (is_null($journey['driver']['start']['datetime']) && is_null($journey['driver']['end']['datetime'])) {
                $journey['driver']['start']['datetime'] = $journey['passenger']['start']['datetime'];
                $journey['driver']['end']['datetime'] = $journey['passenger']['end']['datetime'];
            }
        }

        return $journey;
    }

    public function getCarpoolProof(CarpoolProof $carpoolProof)
    {
        $journeyId = (!is_null($this->prefix) ? $this->prefix : '').(string) $carpoolProof->getId();
        $dataProvider = new DataProvider($this->uri, self::RESSOURCE_GET_ITEM.$journeyId);

        // creation of the headers
        $headers = [
            'Authorization' => 'Bearer '.$this->token,
            'Content-Type' => 'application/json',
        ];

        return $dataProvider->getItem([], $headers);
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
        $this->logger->info('BetaGouv API return');
    }
}
