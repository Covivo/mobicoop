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

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;

/**
 * A postal address.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
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
     */
    private $id;
    
    /**
     * @var string The street address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $streetAddress;
    
    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"read","write"})
     */
    private $postalCode;
    
    /**
     * @var string The locality of the address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $addressLocality;
    
    /**
     * @var string The country of the address.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=100)
     * @Groups({"read","write"})
     */
    private $addressCountry;
    
    /**
     * @var string The latitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $latitude;
    
    /**
     * @var string The longitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","write"})
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $elevation;
    
    /**
     * @var UserAddress[]|null An address may have many users.
     *
     * @ORM\OneToMany(targetEntity="UserAddress", mappedBy="address", cascade={"persist","remove"})
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $userAddresses;

    /**
     * @var Point[]|null The points where the address is referenced.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Point", mappedBy="address", orphanRemoval=true)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $points;

    /**
     * @var Solicitation[]|null The solicitations where the address is set as a starting.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Solicitation", mappedBy="addressFrom")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitationsFrom;

    /**
     * @var Solicitation[]|null The solicitations where the address is set as a destination.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Solicitation", mappedBy="addressTo")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitationsTo;
    
    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
        $this->points = new ArrayCollection();
        $this->solicitationsFrom = new ArrayCollection();
        $this->solicitationsTo = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getStreetAddress(): string
    {
        return $this->streetAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getAddressLocality(): string
    {
        return $this->addressLocality;
    }

    public function getAddressCountry(): string
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

    public function getUserAddresses()
    {
        return $this->userAddresses;
    }

    public function setStreetAddress(string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function setAddressLocality(string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function setAddressCountry(string $addressCountry)
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

    public function setUserAddresses(?array $userAddresses)
    {
        $this->userAddresses = $userAddresses;
    }
    
    public function addUserAddress(UserAddress $userAddress)
    {
        $userAddress->setAddress($this);
        $this->userAddresses->add($userAddress);
    }
    
    public function removeUserAddress(UserAddress $userAddress)
    {
        $this->userAddresses->removeElement($userAddress);
        $userAddress->setAddress(null);
    }

    /**
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setAddress($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getAddress() === $this) {
                $point->setAddress(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsFrom(): Collection
    {
        return $this->solicitationsFrom;
    }

    public function addSolicitationsFrom(Solicitation $solicitationsFrom): self
    {
        if (!$this->solicitationsFrom->contains($solicitationsFrom)) {
            $this->solicitationsFrom[] = $solicitationsFrom;
            $solicitationsFrom->setAddressFrom($this);
        }

        return $this;
    }

    public function removeSolicitationsFrom(Solicitation $solicitationsFrom): self
    {
        if ($this->solicitationsFrom->contains($solicitationsFrom)) {
            $this->solicitationsFrom->removeElement($solicitationsFrom);
            // set the owning side to null (unless already changed)
            if ($solicitationsFrom->getAddressFrom() === $this) {
                $solicitationsFrom->setAddressFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitationsTo(): Collection
    {
        return $this->solicitationsTo;
    }

    public function addSolicitationsTo(Solicitation $solicitationsTo): self
    {
        if (!$this->solicitationsTo->contains($solicitationsTo)) {
            $this->solicitationsTo[] = $solicitationsTo;
            $solicitationsTo->setAddressTo($this);
        }

        return $this;
    }

    public function removeSolicitationsTo(Solicitation $solicitationsTo): self
    {
        if ($this->solicitationsTo->contains($solicitationsTo)) {
            $this->solicitationsTo->removeElement($solicitationsTo);
            // set the owning side to null (unless already changed)
            if ($solicitationsTo->getAddressTo() === $this) {
                $solicitationsTo->setAddressTo(null);
            }
        }

        return $this;
    }
}
