<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

declare(strict_types=1);

namespace App\Geography\Service\Point;

use App\Geography\Ressource\Point;
use App\Geography\Service\Geocoder\MobicoopGeocoder;

class MobicoopGeocoderPointProvider implements PointProvider
{
    protected $mobicoopGeocoder;
    protected $maxResults;
    protected $exclusionTypes;

    public function __construct(MobicoopGeocoder $mobicoopGeocoder)
    {
        $this->mobicoopGeocoder = $mobicoopGeocoder;
        $this->setMaxResults(0);
        $this->setExclusionTypes([]);
    }

    public function setMaxResults(int $maxResults): void
    {
        $this->maxResults = $maxResults;
    }

    public function setExclusionTypes(array $exclusionTypes): void
    {
        $this->exclusionTypes = $exclusionTypes;
    }

    public function search(string $search): array
    {
        return $this->geocoderPointsToPoints(
            $this->mobicoopGeocoder->geocode($search)
        );
    }

    private function geocoderPointsToPoints(array $geocoderPoints): array
    {
        $points = [];
        foreach ($geocoderPoints as $geocoderPoint) {
            if (isset($geocoderPoint['type']) && !in_array($geocoderPoint['type'], $this->exclusionTypes)) {
                $points[] = $this->geocoderPointToPoint($geocoderPoint);
            }
            if ($this->maxResults > 0 && count($points) == $this->maxResults) {
                break;
            }
        }

        return $points;
    }

    private function geocoderPointToPoint(array $item): Point
    {
        $point = new Point();
        $point->setCountry($item['country']);
        $point->setCountryCode($item['country_code']);
        $point->setDistance($item['distance']);
        $point->setHouseNumber($item['house_number']);
        $point->setId($item['id']);
        $point->setLat($item['lat']);
        $point->setLocality($item['locality']);
        $point->setLocalityCode($item['locality_code']);
        $point->setLon($item['lon']);
        $point->setMacroRegion($item['macro_region']);
        $point->setName($item['name']);
        $point->setPopulation($item['population']);
        $point->setPostalCode($item['postal_code']);
        $point->setRegion($item['region']);
        $point->setRegionCode($item['region_code']);
        $point->setStreetName($item['street_name']);
        $point->setType($item['type']);
        $point->setProvider($item['provider']);

        return $point;
    }
}
