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

namespace Mobicoop\Bundle\MobicoopBundle\Match\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Carpool between two peoples after a Matching diagnostic
 */
class MassCarpool
{

    /**
     * @var int $idPerson1
     */
    private $idPerson1;

    /**
     * @var int $idPerson2
     */
    private $idPerson2;

    /**
     * @var MassJourney $journey
     */
    private $journey;

    /**
     * MassCarpool constructor.
     * @param int $idPerson1
     * @param int $idPerson2
     * @param MassJourney $journey
     */
    public function __construct(int $idPerson1, int $idPerson2, MassJourney $journey)
    {
        $this->idPerson1 = $idPerson1;
        $this->idPerson2 = $idPerson2;
        $this->journey = $journey;
    }

    /**
     * @return int
     */
    public function getIdPerson1(): int
    {
        return $this->idPerson1;
    }

    /**
     * @param int $idPerson1
     * @return int
     */
    public function setIdPerson1(int $idPerson1): self
    {
        $this->idPerson1 = $idPerson1;
    }

    /**
     * @return int
     */
    public function getIdPerson2(): int
    {
        return $this->idPerson2;
    }

    /**
     * @param int $idPerson2
     * @return int
     */
    public function setIdPerson2(int $idPerson2): self
    {
        $this->idPerson2 = $idPerson2;
    }

    /**
     * @return MassJourney
     */
    public function getJourney(): MassJourney
    {
        return $this->journey;
    }

    /**
     * @param MassJourney $idPerson2
     * @return MassJourney
     */
    public function setJourney(MassJourney $journey): self
    {
        $this->journey = $journey;
    }

}
