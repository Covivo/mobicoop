<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RdexPlus\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RDEX+ : A User (carpooler)
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class User
{
    const GENGER_MALE = "M";
    const GENGER_FEMALE = "F";
    const GENGER_OTHER = "O";
    
    /**
     * @var string User's id
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $id;

    /**
     * @var string User's alias
     *
     * @Groups({"rdexPlusRead"})
     */
    private $alias;

    /**
     * @var string User's first name
     *
     * @Groups({"rdexPlusRead"})
     */
    private $firstName;

    /**
     * @var string User's last name
     *
     * @Groups({"rdexPlusRead"})
     */
    private $lastName;

    /**
     * @var string User's grade
     *
     * @Groups({"rdexPlusRead"})
     */
    private $grade;

    /**
     * @var string User's picture
     *
     * @Groups({"rdexPlusRead"})
     */
    private $picture;
    
    /**
     * @var string User's gender (F, M, O)
     *
     * @Groups({"rdexPlusRead"})
     */
    private $gender;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }
    
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(?string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }
    
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
    
    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(?string $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
    
    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
}
