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

namespace App\Mapper\Interfaces\DTO\CarpoolProof;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PassengerDTO extends IdentityDTO implements \JsonSerializable
{
    /**
     * @var int
     */
    private $_seats;

    /**
     * @var int
     */
    private $_contribution;

    public function getSeats(): ?int
    {
        return $this->_seats;
    }

    public function setSeats(?int $seats): self
    {
        $this->_seats = $seats;

        return $this;
    }

    public function getContribution(): ?int
    {
        return $this->_contribution;
    }

    public function setContribution(?int $contribution): self
    {
        $this->_contribution = $contribution;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'givenName' => $this->getGivenName(),
                'lastName' => $this->getLastName(),
                'phone' => $this->getPhone(),
                'birthDate' => $this->getBirthDate(),
                'seats' => $this->getSeats(),
                'contribution' => $this->getContribution(),
            ];
    }
}