<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Geography\Entity;

// A Rezopouce Territory status
class RezoPouceTerritoryStatus
{
    public const RZP_TERRITORY_STATUS_NOT_CONSIDERED = 0;
    public const RZP_TERRITORY_STATUS_PONDERING = 1;
    public const RZP_TERRITORY_STATUS_ONGOING = 2;
    public const RZP_TERRITORY_STATUS_WORKING = 3;
    public const RZP_TERRITORY_STATUS_UNSUSCRIBED = 4;

    /**
     * @var int the territory status is
     */
    private $id;

    /**
     * @var string the territory status label
     */
    private $label;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
