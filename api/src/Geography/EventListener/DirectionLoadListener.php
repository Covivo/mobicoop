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

use App\Geography\Service\GeoTools;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Direction Event listener.
 */
class DirectionLoadListener
{
    private $geoTools;

    public function __construct(GeoTools $geoTools)
    {
        $this->geoTools = $geoTools;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $direction = $args->getEntity();
        if (method_exists($direction, 'setCo2')) {
            $direction->setCo2($this->geoTools->getCO2($direction->getDistance()));
        }
    }
}
