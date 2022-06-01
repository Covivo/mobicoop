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

namespace App\Carpool\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Carpooling : result, for a given role, for an ad.
 *
 * ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class ResultRole
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this result role.
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var ResultItem The result item for the outward.
     * @Groups("results")
     */
    private $outward;

    /**
     * @var ResultItem|null The result item for the return trip.
     * @Groups("results")
     */
    private $return;

    /**
     * @var int The number of places offered to display.
     * @Groups("results")
     */
    private $seatsDriver;

    /**
     * @var int The number of places asked to display.
     * @Groups("results")
     */
    private $seatsPassenger;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
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
}
