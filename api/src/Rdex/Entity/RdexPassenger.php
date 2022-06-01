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

namespace App\Rdex\Entity;

/**
 * An RDEX Passenger.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexPassenger implements \JsonSerializable
{
    /**
     * @var string The uuid of the passenger.
     */
    private $uuid;

    /**
     * @var string The pseudonym of the passenger.
     */
    private $alias;

    /**
     * @var string The image of the passenger.
     */
    private $image;

    /**
     * @var string The gender of the driver.
     */
    private $gender;

    /**
     * @var int The number of persons.
     */
    private $persons;

    /**
     * @var int The state of the passenger.
     */
    private $state;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return mixed
     */
    public function getUuid(): mixed
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @return number
     */
    public function getPersons(): number
    {
        return $this->persons;
    }

    /**
     * @return number
     */
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
     * @param number $persons
     */
    public function setPersons($persons)
    {
        $this->persons = $persons;
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
            'uuid'      => $this->getUuid(),
            'alias'     => $this->getAlias(),
            'image'     => $this->getImage(),
            'gender'    => $this->getGender(),
            'persons'   => $this->getPersons(),
            'state'     => $this->getState()
        ];
    }
}
