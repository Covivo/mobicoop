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

namespace Mobicoop\Bundle\MobicoopBundle\User\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A car.
 */
class Car
{
    /**
     * @var int $id The id of this car.
     */
    private $id;
    
    /**
     * @var string The brand of the car.
     */
    private $brand;
    
    /**
     * @var string The model of the car.
     */
    private $model;
    
    /**
     * @var string|null The color of the car.
     */
    private $color;
    
    /**
     * @var string|null The siv of the car.
     */
    private $siv;
    
    /**
     * @var int|null The default number of seats available for carpooling.
     */
    private $seats;
    
    /**
     * @var User The owner of the car.
     *
     * @Assert\NotBlank
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
