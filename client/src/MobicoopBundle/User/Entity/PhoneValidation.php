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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A PhoneValidation
 */
class PhoneValidation implements ResourceInterface
{

    /**
     * @var int The id of this bank account
     */
    
    private $id;

    /**
     * @var string The phone number to validate
     * @Groups({"post"})
     */
    private $phoneNumber;
    
    /**
     * @var bool If the phone number is valid
     */
    private $valid;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function isValid(): ?bool
    {
        return (!is_null($this->valid)) ? $this->valid : false;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }
}
