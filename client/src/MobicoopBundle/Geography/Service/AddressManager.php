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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Address management service.
 */
class AddressManager
{
    private $dataProvider;

    /**
     * Constructor.
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Address::class);
    }

    /**
     * Get an address by its identifier.
     *
     * @param int $id The address id
     *
     * @return null|Address the address found or null if not found
     */
    public function getAddress(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if (200 == $response->getCode()) {
            return $response->getValue();
        }

        return null;
    }

    /**
     * Update an address.
     *
     * @param Address $Address The address to update
     *
     * @return null|Address the address updated or null if error
     */
    public function updateAddress(Address $address)
    {
        if (is_null($address->getId())) {
            $response = $this->dataProvider->post($address);
        } else {
            $response = $this->dataProvider->put($address);
        }
        if (200 == $response->getCode()) {
            return $response->getValue();
        }

        return null;
    }
}
