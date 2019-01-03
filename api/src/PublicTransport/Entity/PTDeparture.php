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
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\Geography\Entity\Address;
use App\Carpool\Entity\IndividualStop;
use Doctrine\ORM\Mapping as ORM;

/**
 * A departure.
 *
 * @ORM\Entity
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/departures/{id}"}}
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTDeparture
{
    
    /**
     * @var int $id The id of this departure.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("pt")
     */
    private $id;
    
    /**
     * @var string|null The name of this departure.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("pt")
     */
    private $name;
    
    /**
     * @var \DateTimeInterface The date and time of this departure.
     *
     * @ORM\Column(type="datetime")
     * @Groups("pt")
     */
    private $date;
   
    /**
     * @var Address The address of this departure.
     *
     * @ORM\ManyToOne(targetEntity="App\Geography\Entity\Address")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("pt")
     */
    private $address;
    
    /**
     * @var IndividualStop|null Individual stop if multimodal using carpool.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\IndividualStop")
     * @Groups({"pt"})
     * @MaxDepth(1)
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
