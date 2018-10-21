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

namespace Mobicoop\Bundle\MobicoopBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\Entity\Resource;

/**
 * A user address.
 */
class UserAddress implements Resource
{
    /**
     * @var int The id of this user address.
     */
    private $id;
    
    /**
     * @var string|null The iri of this user address.
     */
    private $iri;
    
    /**
     * @var string The name of the address for the user.
     *
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var User The user that uses the address.
     *
     * @Assert\NotBlank
     */
    private $user;
    
    /**
     * @var Address The address used.
     *
     * @Assert\NotBlank
     */
    private $address;
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }
}
