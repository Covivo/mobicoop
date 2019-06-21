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

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity;

use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;

/**
 * A public transport line.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLine
{
    /**
     * @var int The id of this line.
     */
    private $id;
    
    /**
     * @var string The name of this line.
     */
    private $name;
    
    /**
     * @var string The number of this line.
     */
    private $number;
    
    /**
     * @var string The origin of this line.
     */
    private $origin;
    
    /**
     * @var string The destination of this line.
     */
    private $destination;
    
    /**
     * @var string The direction of this line if no origin / destination specified.
     */
    private $direction;
    
    /**
     * @var PTCompany The company that manage this line.
     */
    private $ptcompany;
    
    /**
     * @var TravelMode The transport mode of this leg.
     */
    private $travelMode;

    /**
     * @var int The transport mode of this line.
     */
    private $transportMode;

    /**
     * @var string The color of this line.
     */
    private $color;

    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        
        return $this;
    }
    
    public function getNumber(): ?string
    {
        return $this->number;
    }
    
    public function setNumber(?string $number): self
    {
        $this->number = $number;
        
        return $this;
    }
    
    public function getOrigin(): ?string
    {
        return $this->origin;
    }
    
    public function setOrigin(?string $origin): self
    {
        $this->origin = $origin;
        
        return $this;
    }
    
    public function getDestination(): ?string
    {
        return $this->destination;
    }
    
    public function setDestination(?string $destination): self
    {
        $this->destination = $destination;
        
        return $this;
    }
    
    public function getDirection(): ?string
    {
        return $this->direction;
    }
    
    public function setDirection(?string $direction): self
    {
        $this->direction = $direction;
        
        return $this;
    }
    
    public function getPTCompany(): PTCompany
    {
        return $this->ptcompany;
    }
    
    public function setPTCompany(PTCompany $ptcompany): self
    {
        $this->ptcompany = $ptcompany;
        
        return $this;
    }
    
    public function getTravelMode(): TravelMode
    {
        return $this->travelMode;
    }
    
    public function setTravelMode(TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;
        
        return $this;
    }

    public function getTransportMode(): int
    {
        return $this->transportMode;
    }

    public function setTransportMode(int $transportMode): self
    {
        $this->transportMode = $transportMode;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
