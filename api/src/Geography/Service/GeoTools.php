<?php

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

namespace App\Geography\Service;

use App\Geography\Entity\Address;

/**
 * Geographical tools.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoTools
{
    private $params;

    /**
     * Constructor.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @param float $earthRadius Mean earth radius in [m]
     * @return float Distance between points in [m] (same as earthRadius)
     */
    public function haversineGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
    
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
    
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }
    
    /**
     * Get the initial bearing for a direction, in degrees related to the north (0°).
     *
     * @param float $latitudeFrom Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo Latitude of target point in [deg decimal]
     * @param float $longitudeTo Longitude of target point in [deg decimal]
     * @return int  The bearing.
     */
    public function getRhumbLineBearing(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo
    ) {
        //difference in longitudinal coordinates
        $dLon = deg2rad($longitudeTo) - deg2rad($longitudeFrom);
        
        //difference in the phi of latitudinal coordinates
        $dPhi = log(tan(deg2rad($latitudeTo) / 2 + pi() / 4) / tan(deg2rad($latitudeFrom) / 2 + pi() / 4));
        
        //we need to recalculate $dLon if it is greater than pi
        if (abs($dLon) > pi()) {
            if ($dLon > 0) {
                $dLon = (2 * pi() - $dLon) * -1;
            } else {
                $dLon = 2 * pi() + $dLon;
            }
        }
        //return the angle, normalized
        return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
    }

    /**
     * Return the opposite bearing and range for a bearing value
     *
     * @param integer   $bearing    The initial bearing
     * @param integer   $range      The range value
     * @return array                The opposite bearing values
     */
    public function getOppositeBearing(int $bearing, int $range=0)
    {
        $newBearing = abs($bearing-180);
        return [
            'opposite' => $newBearing,
            'min' => (($newBearing-$range)<0) ? ($newBearing-$range+360) : ($newBearing-$range),
            'max' => (($newBearing+$range)>360) ? ($newBearing+$range-360) : ($newBearing+$range)
        ];
    }

    /**
     * Returns the CO2 emission for the given distance
     *
     * @param integer   $distance   The distance in meters
     * @param integer   $round      The precision
     * @return integer  The CO2 emission in grams
     */
    public function getCO2(int $distance, int $round=2)
    {
        //return round(((($distance)/1000) * 7 * 0.0232), $round);
        return round($distance/1000 * 213, $round);
    }

    /**
     * Return logical display label depending on env
     *
     * @param Address $address
     * @return array
     */
    public function getDisplayLabel(Address $address): array
    {
        // Determine the more logical display label considering the params
        // We return an array like this :
        // [
        //     // first line
        //     0 => [
        //         "aaa",
        //         "bbb",
        //         "ccc"
        //     ],
        //     // second line
        //     1 => [
        //         'xxx',
        //         'yyy',
        //         'zzz'
        //     ]
        // ]
        $displayLabelTab = [0=>[],1=>[]];

        // The following parameters are in your env or local env
        
        // FIRST LINE

        // venue
        if (isset($this->params['displayVenue']) && trim($this->params['displayVenue'])==="true") {
            if (trim($address->getVenue())!=="") {
                $displayLabelTab[0][] = $address->getVenue();
            }
        }

        // relay point
        if (isset($this->params['displayRelayPoint']) && trim($this->params['displayRelayPoint'])==="true") {
            if ($relayPoint = $address->getRelayPoint()) {
                if (trim($relayPoint->getName())!=="") {
                    $displayLabelTab[0][] = $relayPoint->getName();
                }
            }
        }

        // street address
        if (isset($this->params['displayStreetAddress']) && trim($this->params['displayStreetAddress'])==="true") {
            if (trim($address->getStreetAddress())!=="") {
                $displayLabelTab[0][] = $address->getStreetAddress();
            }
        }

        // postal code
        if (isset($this->params['displayPostalCode']) && trim($this->params['displayPostalCode'])==="true") {
            if (trim($address->getPostalCode())!=="") {
                $displayLabelTab[0][] = $address->getPostalCode();
            }
        }

        // locality
        if (isset($this->params['displayLocality']) && trim($this->params['displayLocality'])==="true") {
            if (trim($address->getAddressLocality())!=="") {
                $displayLabelTab[0][] = $address->getAddressLocality();
            }
        }

        // SECOND LINE

        // subregion
        if (isset($this->params['displaySubRegion']) && trim($this->params['displaySubRegion'])==="true") {
            if (trim($address->getRegion())!=="") {
                $displayLabelTab[1][] = $address->getRegion();
            }
        }

        // region
        if (isset($this->params['displayRegion']) && trim($this->params['displayRegion'])==="true") {
            if (trim($address->getMacroRegion())!=="") {
                $displayLabelTab[1][] = $address->getMacroRegion();
            }
        }

        // country
        if (isset($this->params['displayCountry']) && trim($this->params['displayCountry'])==="true") {
            if (trim($address->getAddressCountry())!=="") {
                $displayLabelTab[1][] = $address->getAddressCountry();
            }
        }

        // if no separators in local env, we are using comma
        $displaySeparator = ", ";
        if (isset($this->params['displaySeparator'])) {
            $displaySeparator = $this->params['displaySeparator'];
        }
        
        return [implode($displaySeparator, $displayLabelTab[0]),implode($displaySeparator, $displayLabelTab[1])];
    }

}
