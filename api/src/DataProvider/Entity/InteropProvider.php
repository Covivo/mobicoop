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
use App\CarpoolStandard\Entity\Price;
use App\CarpoolStandard\Entity\User;
use App\CarpoolStandard\Interfaces\CarpoolStandardProviderInterface;
use App\DataProvider\Service\DataProvider;
use Symfony\Component\Security\Core\Security;

class InteropProvider implements CarpoolStandardProviderInterface
{
    private const RESSOURCE_MESSAGE = 'messages';
    private const RESSOURCE_BOOKING = 'bookings';

    private $provider;
    private $baseUri;
    private $apiKey;
    private $security;

    public function __construct(
        string $provider,
        string $baseUri,
        string $apiKey,
        Security $security
    ) {
        $this->provider = $provider;
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
        $this->security = $security;
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
                'id' => $message->getFrom()->getExternalId(),
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
                'id' => $message->getTo()->getExternalId(),
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

        return $dataProvider->postCollection($body, $headers);
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
                'id' => (string) $booking->getDriver()->getExternalId(),
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
                'id' => (string) $booking->getPassenger()->getExternalId(),
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
                'amount' => $booking->getPrice()->getAmount(),
                'currency' => $booking->getPrice()->getCurrency(),
            ],
            'driverJourneyId' => $booking->getDriverJourneyId(),
            'passengerJourneyId' => $booking->getPassengerJourneyId(),
            'message' => $booking->getMessage(),
        ];

        return $dataProvider->postCollection($body, $headers);
    }

    public function patchBooking(Booking $booking)
    {
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_BOOKING.'/'.$booking->getId());

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        // Build the body
        $params = [
            'status' => $booking->getStatus(),
            'message' => $booking->getMessage(),
        ];

        return $dataProvider->patchItem(null, $headers, $params);
    }

    public function getBooking(string $bookingId)
    {
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_BOOKING.'/'.$bookingId);
        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        // Build the body
        $body = [
            'bookingId' => $bookingId,
        ];

        $data = json_decode((string) $dataProvider->getItem($body, $headers)->getValue(), true);

        return $this->mapBooking($data);
    }

    public function getBookings(string $userId)
    {
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_BOOKING);

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        // Build the body
        $body = [
            'id' => $userId,
            'driver' => false,
            'passenger' => true,
        ];

        return $dataProvider->getCollection($body, $headers);
    }

    public function getMessages(string $idBooking)
    {
        // $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_MESSAGE.'/'.$idBooking);
        $dataProvider = new DataProvider($this->baseUri.'/'.self::RESSOURCE_MESSAGE);

        $headers = [
            'X-API-KEY' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];
        $data = json_decode((string) $dataProvider->getCollection(null, $headers)->getValue(), true);
        $messages = [];
        foreach ($data as $message) {
            $messages[] = $this->mapMessage($message);
        }

        return $messages;
    }

    public function mapBooking(array $array)
    {
        $booking = new Booking();
        $driver = new User();
        $passenger = new User();
        $price = new Price();

        $driver->setExternalId($array['driver']['id']);
        $driver->setAlias($array['driver']['alias']);
        $driver->setOperator($array['driver']['operator']);
        $driver->setFirstName($array['driver']['firstName']);
        $driver->setLastName($array['driver']['lastName']);
        $driver->setGender($array['driver']['gender']);
        $driver->setGrade($array['driver']['grade']);
        $driver->setPicture($array['driver']['picture']);
        $driver->setVerifiedIdentity($array['driver']['verifiedIdentity']);

        $passenger->setExternalId($array['passenger']['id']);
        $passenger->setAlias($array['passenger']['alias']);
        $passenger->setOperator($array['passenger']['operator']);
        $passenger->setFirstName($array['passenger']['firstName']);
        $passenger->setLastName($array['passenger']['lastName']);
        $passenger->setGender($array['passenger']['gender']);
        $passenger->setGrade($array['passenger']['grade']);
        $passenger->setPicture($array['passenger']['picture']);
        $passenger->setVerifiedIdentity($array['passenger']['verifiedIdentity']);

        $price->setAmount($array['price']['amount']);
        $price->setType($array['price']['type']);
        $price->setCurrency($array['price']['currency']);

        $booking->setDriver($driver);
        $booking->setPassenger($passenger);
        $booking->setPrice($price);
        $booking->setId(Booking::DEFAULT_ID);
        $booking->setExternalId($array['id']);
        $booking->setPassengerPickupDate($array['passengerPickupDate']);
        $booking->setPassengerPickupLat($array['passengerPickupLat']);
        $booking->setPassengerPickupLng($array['passengerPickupLng']);
        $booking->setPassengerDropLat($array['passengerDropLat']);
        $booking->setPassengerDropLng($array['passengerDropLng']);
        $booking->setPassengerPickupAddress($array['passengerPickupAddress']);
        $booking->setPassengerDropAddress($array['passengerDropAddress']);
        $booking->setStatus($array['status']);
        $booking->setDriverJourneyId($array['driverJourneyId']);
        $booking->setPassengerJourneyId($array['passengerJourneyId']);
        $booking->setDuration($array['duration']);
        $booking->setDistance($array['distance']);
        $booking->setWebUrl($array['webUrl']);
        $booking->setRoleDriver(false);

        if ($this->security->getUser()->getId() == intval($booking->getDriver()->getExternalId())) {
            $booking->setRoleDriver(true);
        }

        return $booking;
    }

    public function mapMessage(array $array)
    {
        $message = new Message();

        $message->setTo($this->mapUser($array['to']));
        $message->setFrom($this->mapUser($array['from']));
        $message->setBookingId(isset($array['bookingId']) ? $array['bookingId'] : null);
        $message->setMessage(isset($array['message']) ? $array['message'] : null);
        $message->setRecipientCarpoolerType(isset($array['recipientCarpoolerType']) ? $array['recipientCarpoolerType'] : null);
        $message->setPassengerJourneyId(isset($array['passengerJourneyId']) ? $array['passengerJourneyId'] : null);
        $message->setDriverJourneyId(isset($array['driverJourneyId']) ? $array['driverJourneyId'] : null);
        // $message->setCreatedDateTime(isset($array['createdDate']) ? \DateTime::createFromFormat('U', $array['createdDate']) : null);
        $message->setCreatedDateTime(\DateTime::createFromFormat('U', 1694699357));

        return $message;
    }

    public function mapUser(array $array)
    {
        $user = new User();
        $user->setId(USER::DEFAULT_ID);
        $user->setExternalId(isset($array['id']) ? $array['id'] : null);
        $user->setOperator(isset($array['operator']) ? $array['operator'] : null);
        $user->setAlias(isset($array['alias']) ? $array['alias'] : null);
        $user->setFirstName(isset($array['firstName']) ? $array['firstName'] : null);
        $user->setLastName(isset($array['lastName']) ? $array['lastName'] : null);
        $user->setGrade(isset($array['grade']) ? $array['grade'] : null);
        $user->setPicture(isset($array['picture']) ? $array['picture'] : null);
        $user->setGender(isset($array['gender']) ? $array['gender'] : null);
        $user->setVerifiedIdentity(isset($array['verifiedIdentity']) ? $array['verifiedIdentity'] : null);

        return $user;
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
