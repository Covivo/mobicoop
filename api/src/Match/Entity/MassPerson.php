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

namespace App\Match\Entity;

use App\Geography\Entity\Address;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A mass matching person.
 */
class MassPerson
{
    /**
     * @var string The id of this person.
     * @Assert\NotBlank(groups={"mass"})
     */
    private $id;

    /**
     * @var string|null The first name of the person.
     * @Assert\NotBlank(groups={"mass"})
     */
    private $givenName;

    /**
     * @var string|null The family name of the person.
     * @Assert\NotBlank(groups={"mass"})
     */
    private $familyName;

    /**
     * @var Address The personal address of the person.
     * @Assert\NotBlank(groups={"mass"})
     * @Assert\Valid
     */
    private $personalAddress;

    /**
     * @var Address The work address of the person.
     * @Assert\NotBlank(groups={"mass"})
     * @Assert\Valid
     */
    private $workAddress;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGivenName(): string
    {
        return $this->givenName;
    }

    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getPersonalAddress(): Address
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(Address $address): self
    {
        $this->personalAddress = $address;

        return $this;
    }

    public function getWorkAddress(): Address
    {
        return $this->workAddress;
    }

    public function setWorkAddress(Address $address): self
    {
        $this->workAddress = $address;

        return $this;
    }
}
