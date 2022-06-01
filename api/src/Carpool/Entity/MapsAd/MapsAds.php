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
 */

namespace App\Carpool\Entity\MapsAd;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Map's Ads. Contains a collection of Map's Ad.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MapsAds
{
    /**
     * @var MapsAd[]
     * @Groups({"readCommunityAds"})
     */
    private $mapsAds;

    public function __construct(array $mapsAds = null)
    {
        if (!is_null($mapsAds)) {
            $this->setMapsAds($mapsAds);
        }
    }

    public function getMapsAds(): ?array
    {
        return $this->mapsAds;
    }

    public function setMapsAds(?array $mapsAds): self
    {
        $this->mapsAds = $mapsAds;

        return $this;
    }
}
