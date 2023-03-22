<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Controller;

use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Service\BookingManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for booking related actions.
 *
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
class BookingController extends AbstractController
{
    use HydraControllerTrait;

    private $bookingManager;

    public function __construct(
        BookingManager $bookingManager
    ) {
        $this->bookingManager = $bookingManager;
    }

    /**
     * Post external booking
     * (AJAX POST).
     */
    public function postBooking(Request $request)
    {
        $params = json_decode($request->getContent(), true);

        $booking = $this->bookingManager->postBooking($params);

        if (!is_null($booking)) {
            return $this->json($booking);
        }

        return $this->json('error');
    }
}
