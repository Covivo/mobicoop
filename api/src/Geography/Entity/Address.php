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

namespace App\Geography\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Carpool\Entity\WayPoint;
use App\User\Entity\User;

/**
 * A postal address.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","pt"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "streetAddress", "postalCode", "addressLocality", "addressCountry"}, arguments={"orderParameterName"="order"})
 */
class Address
{
    /**
     * @var int The id of this address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The street address.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $streetAddress;
    
    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $postalCode;
    
    /**
     * @var string|null The locality of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $addressLocality;
    
    /**
     * @var string|null The country of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $addressCountry;
    
    /**
     * @var float|null The latitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $latitude;
    
    /**
     * @var float|null The longitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $elevation;
    
    /**
     * @var string|null The name of this address.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","write","pt"})
     */
    private $name;

    public function __construct($id=null)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getElevation(): ?int
    {
        return $this->elevation;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function setStreetAddress(?string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setAddressLocality(?string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function setAddressCountry(?string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function setLatitude(?float $latitude)
    {
        $this->latitude = $latitude;
    }

    public function setLongitude(?float $longitude)
    {
        $this->longitude = $longitude;
    }

    public function setElevation(?int $elevation)
    {
        $this->elevation = $elevation;
    }
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function setUser(?User $user)
    {
        $user->setAddress($this);
        $this->user = $user;
    }
}
