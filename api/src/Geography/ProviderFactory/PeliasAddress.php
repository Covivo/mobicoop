<?php

declare(strict_types=1);

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
 **************************/

namespace App\Geography\ProviderFactory;

use Geocoder\Model\Address;

/**
 * Address used by Pelias search to handle venues
 */
class PeliasAddress extends Address
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $venue;

    /**
     * @var float|null
     */
    private $distance;

    /**
     * @var string|null
     */
    private $layer;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(?string $id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * {@inheritdoc}
     */
    public function setVenue(?string $venue)
    {
        $this->venue = $venue;
    }

    /**
     * {@inheritdoc}
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * {@inheritdoc}
     */
    public function setDistance(?float $distance)
    {
        $this->distance = $distance;
    }

    /**
     * {@inheritdoc}
     */
    public function getLayer()
    {
        return $this->layer;
    }

    /**
     * {@inheritdoc}
     */
    public function setLayer(?string $layer)
    {
        $this->layer = $layer;
    }
}
