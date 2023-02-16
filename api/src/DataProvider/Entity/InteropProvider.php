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
use GuzzleHttp\Client;

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
        // $dataProvider = new DataProvider($this->baseUri, self::RESSOURCE_MESSAGE);

        // $headers = [
        //     'X-API-KEY' => $this->apiKey,
        // ];
        // // Build the body
        // $body['from'] = $message->getFrom();
        // $body['to'] = $message->getTo();
        // $body['message'] = $message->getMessage();
        // $body['recipientCarpoolerType'] = $message->getRecipientCarpoolerType();
        // $body['driverJourneyId'] = $message->getDriverJourneyId();
        // $body['passengerJourneyId'] = $message->getPassengerJourneyId();
        // $body['bookingId'] = $message->getBookingId();

        // return $dataProvider->postCollection($body, $headers);

        $client = new Client();

        // construct the requested url
        $url = $this->baseUri.'/'.self::RESSOURCE_MESSAGE;
        // request url

        $data = $client->request('POST', $url, [
            'headers' => [
                'X-API-KEY' => $this->apiKey,
            ],
            'body' => [
                'from' => $message->getFrom(),
                'to' => $message->getTo(),
                'message' => $message->getMessage(),
                'recipientCarpoolerType' => $message->getRecipientCarpoolerType(),
                'driverJourneyId' => $message->getDriverJourneyId(),
                'passengerJourneyId' => $message->getPassengerJourneyId(),
                'bookingId' => $message->getBookingId(),
            ],
        ]);

        var_dump($data);

        exit;
    }

    public function postBooking(Booking $booking)
    {
        $dataProvider = new DataProvider($this->baseUri, self::RESSOURCE_BOOKING);

        $headers = [
            'X-API-KEY' => $this->apiKey,
        ];
        // Build the body
        $body['driver'] = $booking->getDriver();
        $body['passenger'] = $booking->getPassenger();
        $body['passengerPickupDate'] = $booking->getPassengerPickupDate();
        $body['passengerPickupLat'] = $booking->getPassengerPickupLat();
        $body['passengerPickupLng'] = $booking->getPassengerPickupLng();
        $body['passengerDropLat'] = $booking->getPassengerDropLat();
        $body['passengerDropLng'] = $booking->getPassengerDropLng();
        $body['passengerPickupAddress'] = $booking->getPassengerPickupAddress();
        $body['passengerDropAddress'] = $booking->getPassengerDropAddress();
        $body['status'] = $booking->getStatus();
        $body['duration'] = $booking->getDuration();
        $body['distance'] = $booking->getDistance();
        $body['webUrl'] = $booking->getWebUrl();
        $body['price'] = $booking->getPrice();
        $body['driverJourneyId'] = $booking->getDriverJourneyId();
        $body['passengerJourneyId'] = $booking->getPassengerJourneyId();

        return $dataProvider->postCollection($body, $headers);
    }
}
