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
 **************************/

namespace App\ExternalJourney\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use App\ExternalJourney\Filter\SearchFilter;

/**
 * An arrival.
 *
 * @ApiResource(
 *     collectionOperations={"get"={"method"="GET"}},
 *     itemOperations={"get"={"method"="GET"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"driver","passenger","from_latitude","from_longitude","to_latitude","to_longitude"})
 */
class ExternalJourney
{
    /**
    * @var int $id of
    */
    private $id;

    private $provider_name;
    private $driver;
    private $passenger;
    private $from_latitude;
    private $from_longitude;
    private $to_latitude;
    private $to_longitude;

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    public function getProvider_Name()
    {
        return $this->provider_name;
    }

    public function setProvider_Name($provider_name)
    {
        return $this->provider_name = $provider_name;
    }

    public function getdriver()
    {
        return $this->driver;
    }

    public function setdriver($driver)
    {
        return $this->driver= $driver;
    }

    public function getpassenger()
    {
        return $this->passenger;
    }

    public function setpassenger($passenger)
    {
        return $this->passenger= $passenger;
    }

    public function getfrom_latitude()
    {
        return $this->from_latitude;
    }

    public function setfrom_latitude($from_latitude)
    {
        return $this->from_latitude= $from_latitude;
    }

    public function getfrom_longitude()
    {
        return $this->from_longitude;
    }

    public function setfrom_longitude($from_longitude)
    {
        return $this->from_longitude= $from_longitude;
    }

    public function getto_latitude()
    {
        return $this->to_latitude;
    }

    public function setto_latitude($to_latitude)
    {
        return $this->to_latitude= $to_latitude;
    }

    public function getto_longitude()
    {
        return $this->to_longitude;
    }

    public function setto_longitude($to_longitude)
    {
        return $this->to_longitude= $to_longitude;
    }
}
