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

namespace App\Match\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An Carpool between two peoples after a Matching diagnostic
 */
class MassCarpool
{

    /**
     * @var MassPerson $person1
     * @Groups({"mass"})
     */
    private $person1;

    /**
     * @var MassPerson $person2
     * @Groups({"mass"})
     */
    private $person2;

    /**
     * @var MassJourney $journey
     * @Groups({"mass"})
     */
    private $journey;

    /**
     * MassCarpool constructor.
     * @param MassPerson $person1
     * @param MassPerson $person2
     * @param MassJourney $journey
     */
    public function __construct(MassPerson $person1, MassPerson $person2, MassJourney $journey)
    {
        $this->person1 = $person1;
        $this->person2 = $person2;
        $this->journey = $journey;
    }

    public function getPerson1(): MassPerson
    {
        return $this->person1;
    }

    public function setPerson1(MassPerson $person1)
    {
        $this->person1 = $person1;
    }

    public function getPerson2(): MassPerson
    {
        return $this->person2;
    }

    public function setPerson2(MassPerson $person2)
    {
        $this->person2 = $person2;
    }

    public function getJourney(): MassJourney
    {
        return $this->journey;
    }

    public function setJourney(MassJourney $journey)
    {
        $this->journey = $journey;
    }
}
