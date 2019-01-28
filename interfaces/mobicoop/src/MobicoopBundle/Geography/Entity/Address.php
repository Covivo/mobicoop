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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * A postal address.
 */
class Address implements Resource
{
    /**
     * @var int The id of this address.
     */
    private $id;

    /**
     * @var string|null The iri of this user.
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string The street address.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $streetAddress;

    /**
     * @var string|null The postal code of the address.
     *
     * @Groups({"post","put"})
     */
    private $postalCode;

    /**
     * @var string The locality of the address.
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressLocality;

    /**
     * @var string The country of the address.
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressCountry;
    
    /**
     * @var float|null The latitude of the address.
     *
     * @Groups({"post","put"})
     */
    private $latitude;
    
    /**
     * @var float|null The longitude of the address.
     *
     * @Groups({"post","put"})
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     */
    private $elevation;
    
    /**
     * @var string|null The name of this address.
     */
    private $name;
    
    /**
     * @var User|null The owner of the address.
     */
    private $user;
    
    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }
    
    public function setStreetAddress(?string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }
    
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }
    
    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }
    
    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }
    
    public function setAddressLocality(?string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }
    
    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }
    
    public function setAddressCountry(?string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }
    
    public function getLatitude(): ?string
    {
        return $this->latitude;
    }
    
    public function setLatitude(?string $latitude)
    {
        $this->latitude = $latitude;
    }
    
    public function getLongitude(): ?string
    {
        return $this->longitude;
    }
    
    public function setLongitude(?string $longitude)
    {
        $this->longitude = $longitude;
    }
    
    public function getElevation(): ?int
    {
        return $this->elevation;
    }
    
    public function setElevation(?int $elevation)
    {
        $this->elevation = $elevation;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user)
    {
        $this->user = $user;
    }
}
