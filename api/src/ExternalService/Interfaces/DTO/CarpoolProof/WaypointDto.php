<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\ExternalService\Interfaces\DTO\CarpoolProof;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WaypointDto
{
    /**
     * @var float
     */
    private $_lat;

    /**
     * @var float
     */
    private $_lon;

    /**
     * @var string
     */
    private $_datetime;

    public function getLat(): ?float
    {
        return $this->_lat;
    }

    public function setLat(?float $lat): self
    {
        $this->_lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->_lon;
    }

    public function setLon(?float $lon): self
    {
        $this->_lon = $lon;

        return $this;
    }

    public function getDatetime(): ?string
    {
        return $this->_datetime;
    }

    public function setDatetime(?string $datetime): self
    {
        $this->_datetime = $datetime;

        return $this;
    }
}
