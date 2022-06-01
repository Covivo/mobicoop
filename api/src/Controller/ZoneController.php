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

namespace App\Controller;

use App\Geography\Entity\Address;
use App\Geography\Service\ZoneManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FOR R&D PURPOSE ONLY.
 *
 * Creation of geographic zones.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ZoneController extends AbstractController
{
    /**
     * @Route("/rd/getzone")
     */
    public function getZones(ZoneManager $zoneManager)
    {
        $address = new Address();
        // $address->setLongitude("6.181201");
        // $address->setLatitude("48.691836");
        $address->setLongitude('6.5');
        $address->setLatitude('7.8');

        $start = microtime(true);
        $zones = $zoneManager->getZonesForAddress($address, 0.01, 1);
        $time_elapsed_secs = microtime(true) - $start;
        var_dump($time_elapsed_secs);
        var_dump($zones);

        exit;
    }
}
