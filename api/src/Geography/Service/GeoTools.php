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
 */

namespace App\Geography\Service;

use App\Geography\Entity\Address;
use App\User\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvalidParameterException extends \Exception
{
}

/**
 * Geographical tools.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class GeoTools
{
    public const METERS_BY_DEGREE = 111319;
    public const RDP_EPSILON = 20;             // Ramer-Douglas-Peucker Epsilon : maximum perpendicular distance for any point from the line between two adjacent points.
    public const RDP_DELTA = 10000;            // Ramer-Douglas-Peucker Epsilon delta (used to convert the epsilon in the coordinates system used, here the gps coordinates system)

    private $translator;
    private $params;

    /**
     * Constructor.
     */
    public function __construct(?TranslatorInterface $translator = null, ?array $params = null)
    {
        $this->translator = $translator;
        $this->params = $params;
    }

    /**
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     *
     * @param float $latitudeFrom  Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo    Latitude of target point in [deg decimal]
     * @param float $longitudeTo   Longitude of target point in [deg decimal]
     * @param float $earthRadius   Mean earth radius in [m]
     *
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
     * @param float $latitudeFrom  Latitude of start point in [deg decimal]
     * @param float $longitudeFrom Longitude of start point in [deg decimal]
     * @param float $latitudeTo    Latitude of target point in [deg decimal]
     * @param float $longitudeTo   Longitude of target point in [deg decimal]
     *
     * @return int the bearing
     */
    public function getRhumbLineBearing(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo
    ) {
        // difference in longitudinal coordinates
        $dLon = deg2rad($longitudeTo) - deg2rad($longitudeFrom);

        // difference in the phi of latitudinal coordinates
        $dPhi = log(tan(deg2rad($latitudeTo) / 2 + pi() / 4) / tan(deg2rad($latitudeFrom) / 2 + pi() / 4));

        // we need to recalculate $dLon if it is greater than pi
        if (abs($dLon) > pi()) {
            if ($dLon > 0) {
                $dLon = (2 * pi() - $dLon) * -1;
            } else {
                $dLon = 2 * pi() + $dLon;
            }
        }
        // return the angle, normalized
        return (rad2deg(atan2($dLon, $dPhi)) + 360) % 360;
    }

    /**
     * Return the opposite bearing and range for a bearing value.
     *
     * @param int $bearing The initial bearing
     * @param int $range   The range value
     *
     * @return array The opposite bearing values
     */
    public function getOppositeBearing(int $bearing, int $range = 0): array
    {
        if ($bearing >= 180) {
            $newBearing = $bearing - 180;
        } else {
            $newBearing = $bearing + 180;
        }

        return [
            'opposite' => $newBearing,
            'min' => (($newBearing - $range) < 0) ? ($newBearing - $range + 360) : ($newBearing - $range),
            'max' => (($newBearing + $range) > 360) ? ($newBearing + $range - 360) : ($newBearing + $range),
        ];
    }

    /**
     * Returns the CO2 emission for the given distance.
     *
     * @param int $distance The distance in meters
     * @param int $round    The precision
     *
     * @return int The CO2 emission in grams
     */
    public function getCO2(int $distance, int $round = 2): int
    {
        // return round(((($distance)/1000) * 7 * 0.0232), $round);
        return round($distance / 1000 * 213, $round);
    }

    /**
     * Get the new latitude of a given point after moving it from a given distance.
     * Only the latitude is needed.
     *
     * @param float $lat  The initial latitude of the point
     * @param int   $dlat The delta latitude in metres
     */
    public function moveGeoLat(float $lat, int $dlat): float
    {
        return $lat + ((1 / self::METERS_BY_DEGREE) * $dlat);
    }

    /**
     * Get the new longitude of a given point after moving it from a given distance.
     * Longitude AND latitude are needed.
     *
     * @param float $lon  The initial longitude of the point
     * @param float $lat  The initial latitude of the point
     * @param int   $dlon The delta longitude in metres
     */
    public function moveGeoLon(float $lon, float $lat, int $dlon): float
    {
        return $lon + (((1 / self::METERS_BY_DEGREE) * $dlon) / cos($lat * 0.018)); // pi / 180 = 0.018
    }

    /**
     * Return logical display label depending on env.
     */
    public function getDisplayLabel(Address $address, ?UserInterface $user = null): array
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
        $displayLabelTab = [0 => [], 1 => []];

        // The following parameters are in your env or local env

        // FIRST LINE

        // venue
        if (isset($this->params['displayVenue']) && 'true' === trim($this->params['displayVenue'])) {
            if ('' !== trim($address->getVenue())) {
                $displayLabelTab[0][] = $address->getVenue();
            }
        }

        // relay point
        if (isset($this->params['displayRelayPoint']) && 'true' === trim($this->params['displayRelayPoint'])) {
            if ($relayPoint = $address->getRelayPoint()) {
                if ('' !== trim($relayPoint->getName())) {
                    $displayLabelTab[0][] = $relayPoint->getName();
                }
            }
        }

        // named address
        if (isset($this->params['displayNamed']) && 'true' === trim($this->params['displayNamed'])) {
            if ('' !== trim($address->getName()) && !is_null($address->getUser()) && !is_null($user) && $user instanceof User && $user->getId() == $address->getUser()->getId()) {
                $displayLabelTab[0][] = !is_null($this->translator) ? $this->translator->trans($address->getName()) : $address->getName();
            }
        }

        // event
        if (isset($this->params['displayEvent']) && 'true' === trim($this->params['displayEvent'])) {
            if ($event = $address->getEvent()) {
                if ('' !== trim($event->getName())) {
                    $displayLabelTab[0][] = $event->getName();
                }
            }
        }

        // street address
        $streetAddressFound = false;
        if (isset($this->params['displayStreetAddress']) && 'true' === trim($this->params['displayStreetAddress'])) {
            if ('' !== trim($address->getStreetAddress())) {
                $streetAddressFound = true;
                $displayLabelTab[0][] = $address->getStreetAddress();
            }
        }

        // postal code
        $postalCodeFound = false;
        if (isset($this->params['displayPostalCode']) && 'true' === trim($this->params['displayPostalCode'])) {
            if ('' !== trim($address->getPostalCode())) {
                $postalCodeFound = true;
                $displayLabelTab[0][] = $address->getPostalCode();
            }
        }

        // locality
        $addressLocalityFound = false;
        if (isset($this->params['displayLocality']) && 'true' === trim($this->params['displayLocality'])) {
            if ('' !== trim($address->getAddressLocality())) {
                $addressLocalityFound = true;
                $displayLabelTab[0][] = ucfirst(strtolower($address->getAddressLocality()));
            }
        }

        // Better looking when no address but just postal code and addressLocality
        if (!$streetAddressFound && $postalCodeFound && $addressLocalityFound) {
            $firstEl = $displayLabelTab[0][count($displayLabelTab[0]) - 2];
            $secondEl = $displayLabelTab[0][count($displayLabelTab[0]) - 1];
            $displayLabelTab[0][count($displayLabelTab[0]) - 2] = $secondEl;
            $displayLabelTab[0][count($displayLabelTab[0]) - 1] = $firstEl;
        }

        // SECOND LINE

        // subregion
        if (isset($this->params['displaySubRegion']) && 'true' === trim($this->params['displaySubRegion'])) {
            if ('' !== trim($address->getRegion())) {
                $displayLabelTab[1][] = ucfirst(strtolower($address->getRegion()));
            }
        }

        // region
        if (isset($this->params['displayRegion']) && 'true' === trim($this->params['displayRegion'])) {
            if ('' !== trim($address->getMacroRegion())) {
                $displayLabelTab[1][] = ucfirst(strtolower($address->getMacroRegion()));
            }
        }

        // country
        if (isset($this->params['displayCountry']) && 'true' === trim($this->params['displayCountry'])) {
            if ('' !== trim($address->getAddressCountry())) {
                $displayLabelTab[1][] = ucfirst(strtolower($address->getAddressCountry()));
            }
        }

        // if no separators in local env, we are using comma
        $displaySeparator = ', ';
        if (isset($this->params['displaySeparator'])) {
            $displaySeparator = $this->params['displaySeparator'];
        }

        return [implode($displaySeparator, $displayLabelTab[0]), implode($displaySeparator, $displayLabelTab[1])];
    }

    /**
     * Return a simplified array of points from an array of addresses.
     * /!\ This is used for approximate purpose only : The RDP algorithm works only on a 2D coordinates system, not on a 3D sphere system /!\.
     *
     * @param array $addresses The array of addresses representing the line
     *
     * @return array The array of addresses, simplified
     */
    public function getSimplifiedPoints(array $addresses): array
    {
        $line = [];
        foreach ($addresses as $address) {
            $line[] = [
                $address->getLongitude(),
                $address->getLatitude(),
            ];
        }

        return $this->RamerDouglasPeucker2d($line, self::RDP_EPSILON / self::RDP_DELTA);
    }

    /**
     * RamerDouglasPeucker2d.
     *
     * Reduces the number of points on a polyline by removing those that are
     * closer to the line than the distance $epsilon.
     *
     * @param array $pointList an array of arrays, where each internal array
     *                         is one point on the polyline, specified by two numeric coordinates
     * @param float $epsilon   The distance threshold to use. The unit should be
     *                         the same as that of the coordinates of the points in $pointList.
     *
     * @return array $pointList An array of arrays, with the same format as the
     *               original argument $pointList. Each point returned in the result array will
     *               retain all its original data.
     */
    public static function RamerDouglasPeucker2d($pointList, $epsilon): array
    {
        if ($epsilon <= 0) {
            throw new InvalidParameterException('Non-positive epsilon.');
        }
        if (count($pointList) < 2) {
            return $pointList;
        }
        // Find the point with the maximum distance
        $dmax = 0;
        $index = 0;
        $totalPoints = count($pointList);
        for ($i = 1; $i < ($totalPoints - 1); ++$i) {
            $d = self::perpendicularDistance2d(
                $pointList[$i][0],
                $pointList[$i][1],
                $pointList[0][0],
                $pointList[0][1],
                $pointList[$totalPoints - 1][0],
                $pointList[$totalPoints - 1][1]
            );
            if ($d > $dmax) {
                $index = $i;
                $dmax = $d;
            }
        }
        $resultList = [];
        // If max distance is greater than epsilon, recursively simplify
        if ($dmax >= $epsilon) {
            // Recursive call on each 'half' of the polyline
            $recResults1 = self::RamerDouglasPeucker2d(
                array_slice($pointList, 0, $index + 1),
                $epsilon
            );
            $recResults2 = self::RamerDouglasPeucker2d(
                array_slice($pointList, $index, $totalPoints - $index),
                $epsilon
            );
            // Build the result list
            $resultList = array_merge(
                array_slice(
                    $recResults1,
                    0,
                    count($recResults1) - 1
                ),
                array_slice(
                    $recResults2,
                    0,
                    count($recResults2)
                )
            );
        } else {
            $resultList = [$pointList[0], $pointList[$totalPoints - 1]];
        }
        // Return the result
        return $resultList;
    }

    /**
     * The following source code is taken from this repository : https://github.com/david-r-edgar/RDP-PHP
     * Not included via composer because of a failure from Packagist...
     * All the credits goes to David Edgar !
     *
     * @param mixed $ptX
     * @param mixed $ptY
     * @param mixed $l1x
     * @param mixed $l1y
     * @param mixed $l2x
     * @param mixed $l2y
     */

    /**
     * An implementation of the Ramer-Douglas-Peucker algorithm for reducing
     * the number of points on a polyline.
     *
     * For more information, see:
     * http://en.wikipedia.org/wiki/Ramer%E2%80%93Douglas%E2%80%93Peucker_algorithm
     *
     * @author David Edgar
     * @license PD The author has placed this work in the Public Domain, thereby
     * relinquishing all copyrights.
     * You may use, modify, republish, sell or give away this work without prior
     * consent.
     * This implementation comes with no warranty or guarantee of fitness for any
     * purpose.
     */

    /**
     * Finds the perpendicular distance from a point to a straight line.
     *
     * The coordinates of the point are specified as $ptX and $ptY.
     *
     * The line passes through points l1 and l2, specified respectively with
     * their coordinates $l1x and $l1y, and $l2x and $l2y
     *
     * @param float $ptX X coordinate of the point
     * @param float $ptY Y coordinate of the point
     * @param float $l1x X coordinate of point on the line l1
     * @param float $l1y Y coordinate of point on the line l1
     * @param float $l2x X coordinate of point on the line l2
     * @param float $l2y Y coordinate of point on the line l2
     *
     * @return float the distance from the point to the line
     */
    protected static function perpendicularDistance2d(
        $ptX,
        $ptY,
        $l1x,
        $l1y,
        $l2x,
        $l2y
    ) {
        $result = 0;
        if ($l2x == $l1x) {
            // vertical lines - treat this case specially to avoid dividing
            // by zero
            $result = abs($ptX - $l2x);
        } else {
            $slope = (($l2y - $l1y) / ($l2x - $l1x));
            $passThroughY = (0 - $l1x) * $slope + $l1y;
            $result = (abs(($slope * $ptX) - $ptY + $passThroughY)) /
                      (sqrt($slope * $slope + 1));
        }

        return $result;
    }

    /**
     * Get the length of a longitude degree depending on the latitude.
     */
    private function getDegreeValueForLat(float $lat)
    {
        return cos($lat) * self::METERS_BY_DEGREE;
    }
}
