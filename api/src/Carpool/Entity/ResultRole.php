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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Carpooling : result, for a given role, for a search / ad post.
 *
 * @ApiResource(
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
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this result role.
     */
    private $id;

    /**
     * @var ResultItem The result item for the outward.
     * @Groups("read")
     */
    private $outward;

    /**
     * @var ResultItem|null The result item for the return trip.
     * @Groups("read")
     */
    private $return;

    /**
     * @var int The number of places offered / requested.
     * @Groups("read")
     */
    private $seats;

    /**
     * @var string The computed price for the common distance carpooled.
     * @Groups("read")
     */
    private $price;

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

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }
    
    public function setPrice(?string $price)
    {
        $this->price = $price;
    }
}
