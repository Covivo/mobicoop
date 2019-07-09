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

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * A Mass Matching.
 */
class MassMatching implements ResourceInterface
{
    /**
     * @var int The id of this matching.
     */
    private $id;

    /**
     * @var string|null The iri of this matching.
     */
    private $iri;

    /**
     * @var MassPerson The first person.
     */
    private $massPerson1;

    /**
     * @var int The first person.
     */
    private $massPerson1Id;

    /**
     * @var MassPerson The second person.
     */
    private $massPerson2;

    /**
     * @var int The second person.
     */
    private $massPerson2Id;

    /**
     * @var int The total distance of the direction in meter.
     */
    private $distance;

    /**
     * @var int The total duration of the direction in milliseconds.
     */
    private $duration;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getMassPerson1(): MassPerson
    {
        return $this->massPerson1;
    }

    public function setMassPerson1(MassPerson $massPerson1): self
    {
        $this->massPerson1 = $massPerson1;

        return $this;
    }

    public function getMassPerson1Id(): int
    {
        return $this->massPerson1Id;
    }

    public function setMassPerson1Id($massPerson1Id): self
    {
        $this->massPerson1Id = $massPerson1Id;

        return $this;
    }

    public function getMassPerson2(): MassPerson
    {
        return $this->massPerson2;
    }

    public function setMassPerson2(MassPerson $massPerson2): self
    {
        $this->massPerson2 = $massPerson2;

        return $this;
    }

    public function getMassPerson2Id(): int
    {
        return $this->massPerson2Id;
    }

    public function setMassPerson2Id($massPerson2Id): self
    {
        $this->massPerson2Id = $massPerson2Id;

        return $this;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function setDistance(int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
