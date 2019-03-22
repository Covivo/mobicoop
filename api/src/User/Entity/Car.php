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

namespace App\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A car.
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
 */
class Car
{
    /**
     * @var int $id The id of this car.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;
    
    /**
     * @var string The brand of the car.
     *
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $brand;
    
    /**
     * @var string The model of the car.
     *
     * @ORM\Column(type="string", length=45)
     * @Groups({"read","write"})
     */
    private $model;
    
    /**
     * @var string|null The color of the car.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","write"})
     */
    private $color;
    
    /**
     * @var string|null The siv of the car.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","write"})
     */
    private $siv;
    
    /**
     * @var int|null The default number of seats available for carpooling.
     *
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $seats;
    
    /**
     * @var float|null The price per km.
     *
     * @ORM\Column(type="decimal", precision=4, scale=2, nullable=true)
     * @Groups({"read","write"})
     */
    private $priceKm;
    
    /**
     * @var User The owner of the car.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="cars")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getBrand(): string
    {
        return $this->brand;
    }
    
    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        
        return $this;
    }

    public function getModel(): string
    {
        return $this->model;
    }
    
    public function setModel(string $model): self
    {
        $this->model = $model;
        
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }
    
    public function setColor(?string $color): self
    {
        $this->color = $color;
        
        return $this;
    }

    public function getSiv(): ?string
    {
        return $this->siv;
    }
    
    public function setSiv(?string $siv): self
    {
        $this->siv = $siv;
        
        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }
    
    public function setSeats(?int $seats): self
    {
        $this->seats = $seats;
        
        return $this;
    }
    
    public function getPriceKm(): ?string
    {
        return $this->priceKm;
    }
    
    public function setPriceKm(?string $priceKm)
    {
        $this->priceKm = $priceKm;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
}
