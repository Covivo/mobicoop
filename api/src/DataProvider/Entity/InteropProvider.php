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

use App\CarpoolStandard\Entity\Booking;
use App\CarpoolStandard\Entity\Message;
use App\CarpoolStandard\Interfaces\CarpoolStandardProviderInterface;
use App\DataProvider\Service\DataProvider;

class InteropProvider implements CarpoolStandardProviderInterface
{
    private const RESSOURCE_MESSAGE = 'messages';
    private const RESSOURCE_BOOKING = 'bookings';

    private $provider;
    private $baseUri;
    private $apiKey;

    public function __construct(string $provider, string $baseUri, string $apiKey)
    {
        $this->provider = $provider;
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
    }

    public function postMessage(Message $message)
    {
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_MESSAGE);

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        // Build the body
        $body = [
            'from' => [
                'id' => $message->getFrom()->getId(),
                'operator' => $message->getFrom()->getOperator(),
                'alias' => preg_replace('/\s+/', '-', $message->getFrom()->getAlias()),
                'firstName' => $message->getFrom()->getFirstName(),
                'lastName' => $message->getFrom()->getLastName(),
                'grade' => $message->getFrom()->getGrade(),
                'picture' => $message->getFrom()->getPicture(),
                'gender' => $message->getFrom()->getGender(),
                'verifiedIdentity' => $message->getFrom()->getVerifiedIdentity(),
            ],
            'to' => [
                'id' => $message->getTo()->getId(),
                'operator' => $message->getTo()->getOperator(),
                'alias' => preg_replace('/\s+/', '-', $message->getTo()->getAlias()),
                'firstName' => $message->getTo()->getFirstName(),
                'lastName' => $message->getTo()->getLastName(),
                'grade' => $message->getTo()->getGrade(),
                'picture' => $message->getTo()->getPicture(),
                'gender' => $message->getTo()->getGender(),
                'verifiedIdentity' => $message->getTo()->getVerifiedIdentity(),
            ],
            'message' => $message->getMessage(),
            'recipientCarpoolerType' => $message->getRecipientCarpoolerType(),
            'driverJourneyId' => $message->getDriverJourneyId(),
            'passengerJourneyId' => $message->getPassengerJourneyId(),
            'bookingId' => $message->getBookingId(),
        ];

        $response = $dataProvider->postCollection(json_encode($body), $headers);
        var_dump($response);

        exit;
    }

    public function postBooking(Booking $booking)
    {
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_BOOKING);

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        // Build the body
        $body = [
            'id' => (string) $this->_generateUuid(),
            'driver' => [
                'id' => (string) $booking->getDriver()->getId(),
                'operator' => $booking->getDriver()->getOperator(),
                'alias' => preg_replace('/\s+/', '-', $booking->getDriver()->getAlias()),
                'firstName' => $booking->getDriver()->getFirstName(),
                'lastName' => $booking->getDriver()->getLastName(),
                'grade' => $booking->getDriver()->getGrade(),
                'picture' => $booking->getDriver()->getPicture(),
                'gender' => $booking->getDriver()->getGender(),
                'verifiedIdentity' => $booking->getDriver()->getVerifiedIdentity(),
            ],
            'passenger' => [
                'id' => (string) $booking->getPassenger()->getId(),
                'operator' => $booking->getPassenger()->getOperator(),
                'alias' => preg_replace('/\s+/', '-', $booking->getPassenger()->getAlias()),
                'firstName' => $booking->getPassenger()->getFirstName(),
                'lastName' => $booking->getPassenger()->getLastName(),
                'grade' => $booking->getPassenger()->getGrade(),
                'picture' => $booking->getPassenger()->getPicture(),
                'gender' => $booking->getPassenger()->getGender(),
                'verifiedIdentity' => $booking->getPassenger()->getVerifiedIdentity(),
            ],
            'passengerPickupDate' => $booking->getPassengerPickupDate(),
            'passengerPickupLat' => $booking->getPassengerPickupLat(),
            'passengerPickupLng' => $booking->getPassengerPickupLng(),
            'passengerDropLat' => $booking->getPassengerDropLat(),
            'passengerDropLng' => $booking->getPassengerDropLng(),
            'passengerPickupAddress' => $booking->getPassengerPickupAddress(),
            'passengerDropAddress' => $booking->getPassengerDropAddress(),
            'status' => $booking->getStatus(),
            'duration' => $booking->getDuration(),
            'distance' => $booking->getDistance(),
            'webUrl' => $booking->getWebUrl(),
            'price' => [
                'type' => $booking->getPrice()->getType(),
                'operator' => $booking->getPrice()->getAmount(),
                'alias' => $booking->getPrice()->getCurrency(),
            ],
            'driverJourneyId' => $booking->getDriverJourneyId(),
            'passengerJourneyId' => $booking->getPassengerJourneyId(),
        ];

        $response = $dataProvider->postCollection(json_encode($body), $headers);

        var_dump($response);

        exit;
    }

    private function _generateUuid()
    {
        // Generate a random string of bytes
        $bytes = openssl_random_pseudo_bytes(16);

        // Convert the bytes to a hexadecimal string
        $hex = bin2hex($bytes);

        // Format the hexadecimal string as a UUID
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
