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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * Carpooling : result, for a given role, for a search / ad post.
 */
class ResultRole implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this result role.
     */
    private $id;

    /**
     * @var string|null The iri of this result role.
     */
    private $iri;

    /**
     * @var ResultItem The result item for the outward.
     */
    private $outward;

    /**
     * @var ResultItem|null The result item for the return trip.
     */
    private $return;

    /**
     * @var int The number of places offered to display.
     */
    private $seatsDriver;

    /**
     * @var int The number of places asked to display.
     */
    private $seatsPassenger;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/result_roles/".$id);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
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

    public function getOutward(): ?ResultItem
    {
        return $this->outward;
    }

    public function setOutward(?ResultItem $outward): self
    {
        $this->outward = $outward;

        return $this;
    }

    public function getReturn(): ?ResultItem
    {
        return $this->return;
    }

    public function setReturn(?ResultItem $return): self
    {
        $this->return = $return;

        return $this;
    }

    public function getSeatsDriver(): ?int
    {
        return $this->seatsDriver;
    }

    public function setSeatsDriver(int $seatsDriver): self
    {
        $this->seatsDriver = $seatsDriver;

        return $this;
    }

    public function getSeatsPassenger(): ?int
    {
        return $this->seatsPassenger;
    }

    public function setSeatsPassenger(int $seatsPassenger): self
    {
        $this->seatsPassenger = $seatsPassenger;

        return $this;
    }

    // If you want more info you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'outward'           => $this->getOutward(),
            'return'            => $this->getReturn(),
            'seatsDriver'       => $this->getSeatsDriver(),
            'seatsPassenger'    => $this->getSeatsPassenger()
        ];
    }
}
