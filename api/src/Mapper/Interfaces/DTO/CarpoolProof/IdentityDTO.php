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
class IdentityDTO
{
    /**
     * @var int
     */
    private $_id;

    /**
     * @var string
     */
    private $_givenName;

    /**
     * @var string
     */
    private $_lastName;

    /**
     * @var string
     */
    private $_phone;

    /**
     * @var \DateTimeInterface
     */
    private $_birthDate;

    public function getId(): ?int
    {
        return $this->_id;
    }

    public function setId(?int $id): self
    {
        $this->_id = $id;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->_givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->_givenName = $givenName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->_lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->_lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->_phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->_phone = $phone;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->_birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): self
    {
        $this->_birthDate = $birthDate;

        return $this;
    }
}
