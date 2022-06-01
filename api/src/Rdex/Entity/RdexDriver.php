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

namespace App\Rdex\Entity;

/**
 * An RDEX Driver.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexDriver implements \JsonSerializable
{
    /**
     * @var string the uuid of the driver
     */
    private $uuid;

    /**
     * @var string the pseudonym of the driver
     */
    private $alias;

    /**
     * @var string the image of the driver
     */
    private $image;

    /**
     * @var string the gender of the driver
     */
    private $gender;

    /**
     * @var int the number of available seats
     */
    private $seats;

    /**
     * @var int the state of the driver
     */
    private $state;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function getUuid(): mixed
    {
        return $this->uuid;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getSeats(): number
    {
        return $this->seats;
    }

    public function getState(): number
    {
        return $this->state;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @param number $seats
     */
    public function setSeats($seats)
    {
        $this->seats = $seats;
    }

    /**
     * @param number $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    public function jsonSerialize(): mixed
    {
        return
        [
            'uuid' => $this->getUuid(),
            'alias' => $this->getAlias(),
            'image' => $this->getImage(),
            'gender' => $this->getGender(),
            'seats' => $this->getSeats(),
            'state' => $this->getState(),
        ];
    }
}
