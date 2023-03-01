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

namespace App\CarpoolStandard\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\CarpoolStandard\Entity\Booking;
use App\CarpoolStandard\Service\BookingManager;
use Symfony\Component\Security\Core\Security;

/**
 * Post a Message.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
final class BookingCollectionDataPersister implements ContextAwareDataPersisterInterface
{
    private $security;
    private $bookingManager;

    public function __construct(
        Security $security,
        BookingManager $bookingManager
    ) {
        $this->security = $security;
        $this->bookingManager = $bookingManager;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Booking && isset($context['collection_operation_name']) && 'carpool_standard_post' == $context['collection_operation_name'];
    }

    public function persist($data, array $context = [])
    {
        return $this->bookingManager->postBooking($data);
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
