<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Entity\MapsAd;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Map's Ad waypoint.
 */
class MapsAdWaypoint
{
    /**
     * @var string
     * @Groups({"readCommunityAds"})
    */
    private $latitude;

    /**
     * @var string
     * @Groups({"readCommunityAds"})
    */
    private $longitude;

    /**
     * @var string
     * @Groups({"readCommunityAds"})
     */
    private $displayLabel;


    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getDisplayLabel(): ?string
    {
        return $this->displayLabel;
    }

    public function setDisplayLabel(?string $displayLabel): self
    {
        $this->displayLabel = $displayLabel;

        return $this;
    }
}
