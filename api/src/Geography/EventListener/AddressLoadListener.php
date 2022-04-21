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

namespace App\Geography\EventListener;

use App\Geography\Entity\Address;
use App\Geography\Service\GeoTools;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

/**
 * Address Load Event listener.
 */
class AddressLoadListener
{
    private $geoTools;
    private $security;

    public function __construct(GeoTools $geoTools, Security $security)
    {
        $this->geoTools = $geoTools;
        $this->security = $security;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $address = $args->getEntity();
        if ($address instanceof Address) {
            $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $this->security->getUser()));
            if ($address->getEvent()) {
                $address->setName($address->getEvent()->getName());
            }
        }
    }
}
