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

namespace App\Geography\EventListener;

use App\Geography\Entity\Address;
use App\Geography\Service\AddressManager;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * Address Write Event listener
 */
class AddressWriteListener
{
    private $addressManager;

    public function __construct(AddressManager $addressManager)
    {
        $this->addressManager = $addressManager;
    }

    public function setTerritories(Address $address, LifecycleEventArgs $args)
    {
        // we create the link to territories only for some selected entities
        $this->addressManager->createAddressTerritoriesForUsefulEntity($address, false);
    }
}
