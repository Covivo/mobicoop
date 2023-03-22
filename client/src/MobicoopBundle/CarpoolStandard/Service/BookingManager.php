<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\Booking;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\Price;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Component\Security\Core\Security;

class BookingManager
{
    private $dataProvider;
    private $userManager;
    private $security;

    /**
     * Constructor.
     *
     * @throws \ReflectionException
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager, Security $security)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Booking::class, Booking::RESOURCE_NAME);
        $this->dataProvider->setFormat(DataProvider::RETURN_OBJECT);
        $this->security = $security;
        $this->userManager = $userManager;
    }

    public function postBooking(array $data)
    {
        $booking = $this->createBookingFromResult($data);

        $response = $this->dataProvider->post($booking);
        if (201 != $response->getCode()) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    public function createBookingFromResult(array $data)
    {
        $booking = new Booking();
        $driver = new User();
        $passenger = new User();
        $price = new Price();

        $driver->setExternalId($data['carpooler']['externalJourneyUserId']);
        $driver->setAlias($data['carpooler']['givenName']);
        $driver->setOperator($data['externalOperator']);

        $passenger->setExternalId($this->userManager->getLoggedUser()->getId());
        $passenger->setAlias($this->userManager->getLoggedUser()->getGivenName().' '.$this->userManager->getLoggedUser()->getShortFamilyName());
        $passenger->setFirstName($this->userManager->getLoggedUser()->getGivenName());
        $passenger->setLastName($this->userManager->getLoggedUser()->getFamilyName());
        $passenger->setOperator('mobicoop.fr');

        $price->setAmount($data['roundedPrice']);
        $price->setType(Price::TYPE_UNKNOWN);

        $booking->setDriver($driver);
        $booking->setPassenger($passenger);
        $booking->setPrice($price);
        $booking->setPassengerPickupDate(1679499383);
        $booking->setPassengerPickupLat($data['origin']['latitude']);
        $booking->setPassengerPickupLng($data['origin']['longitude']);
        $booking->setPassengerDropLat($data['destination']['latitude']);
        $booking->setPassengerDropLng($data['destination']['latitude']);
        $booking->setPassengerPickupAddress($data['origin']['streetAddress']);
        $booking->setPassengerDropAddress($data['destination']['streetAddress']);
        $booking->setStatus(Booking::WAITING_CONFIRMATION);
        $booking->setDriverJourneyId($data['externalJourneyId']);

        return $booking;
    }
}
