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

namespace App\Geography\Interfaces;

use App\Geography\Entity\Direction;

/**
 * Geographic router interface.
 *
 * A geographic router entity class must implement all these methods in order to retrieve data and populate entities for geographic services.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
interface GeorouterInterface
{
    public const MODE_SYNC = 1;             // synchronous
    public const MODE_ASYNC = 2;            // simple asynchronous
    public const MODE_MULTIPLE_ASYNC = 3;   // multiple asynchronous
    
    public const RETURN_TYPE_OBJECT = 1;    // the georouter will return results as objects
    public const RETURN_TYPE_ARRAY = 2;     // the georouter will return results as arrays
    public const RETURN_TYPE_RAW = 3;       // the georouter will return raw results (no transformation)

    /**
     * Get multiple directions.
     * Needs an array of array of addresses :
     * [
     *      owner1 => [
     *          [
     *              address1,
     *              address2,
     *              address3,
     *              ...
     *              addressN
     *          ],
     *          ...
     *          [
     *              address1,
     *              address3,
     *              address2,
     *              ...
     *              addressN
     *          ]
     *      ]
     *      ...
     *      ownerN => ...
     * ]
     * Each array of addresses represents a variant of a direction, it is useful to check the best direction between a set of points.
     *
     * @param array $multiPoints    The array of array of addresses
     * @param integer $mode         The sync mode to use
     * @return array    The found directions indexed by owner
     */
    public function getMultipleDirections(array $multiPoints, int $mode): array;

    /**
     * Get directions.
     * Needs an array of addresses :
     * [
     *      address1,
     *      address2,
     *      address3,
     *      ...
     *      addressN
     * ]
     *
     * @param array $points     The array of addresses
     * @param integer $mode     The sync mode to use
     * @return array    The found directions
     */
    public function getDirections(array $points, int $mode): array;

    /**
     * Avoid motorway
     *
     * @param boolean $avoidMotorway    Avoid motorway
     * @return void
     */
    public function setAvoidMotorway(bool $avoidMotorway): void;

    /**
     * Avoid toll
     *
     * @param boolean $avoidToll    Avoid toll
     * @return void
     */
    public function setAvoidToll(bool $avoidToll): void;

    /**
     * Set if the georouter returns detailed durations.
     *
     * @param boolean $detailDuration   Detailed duration
     * @return void
     */
    public function setDetailDuration(bool $detailDuration): void;

    /**
     * Get the points only
     *
     * @param boolean $pointsOnly   Get the points only
     * @return void
     */
    public function setPointsOnly(bool $pointsOnly): void;

    /**
     * Set the return type
     *
     * @param integer $returnType   The return type
     * @return void
     */
    public function setReturnType(int $returnType): void;

    /**
     * Deserializes the data returned by the provider to a Direction object.
     *
     * @param array $data       The data to deserialize.
     * @return Direction        The resulting direction.
     */
    public function deserializeDirection(array $data): Direction;

    /**
     * Deserializes geographical points to Addresses.
     *
     * @param string $data      The data to deserialize
     */
    public function deserializePoints(string $data);
}
