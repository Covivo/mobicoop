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

namespace App\PublicTransport\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;
use Doctrine\ORM\Mapping as ORM;
use App\Carpool\Entity\IndividualStop;

/**
 * A public transport arrival.
 *
 * @ORM\Entity
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={
 *          "get"={
 *              "path"="/arrivals/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTArrival
{
    /**
     * @var int The id of this arrival.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of this arrival.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("pt")
     */
    private $name;
    
    /**
     * @var \DateTimeInterface The date and time of this arrival.
     *
     * @ORM\Column(type="datetime")
     * @Groups("pt")
     */
    private $date;
   
    /**
     * @var Address The address of this arrival.
     *
     * @ORM\ManyToOne(targetEntity="App\Geography\Entity\Address")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $address;
    
    /**
     * @var IndividualStop|null Individual stop if multimodal using carpool.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\IndividualStop")
     */
    private $individualStop;
    
    public function __construct($id)
    {
        $this->id = $id;
    }
    
    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName(?string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
    
    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        
        return $this;
    }
    
    public function getAddress(): Address
    {
        return $this->address;
    }
    
    public function setAddress(Address $address): self
    {
        $this->address = $address;
        
        return $this;
    }
    
    public function getIndividualStop(): ?IndividualStop
    {
        return $this->individualStop;
    }
    
    public function setIndividualStop(?IndividualStop $individualStop): self
    {
        $this->individualStop = $individualStop;
        
        return $this;
    }
}
