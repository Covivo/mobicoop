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
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * A public transport step (by walk or public transport).
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/steps/{id}"}}
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTStep
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var int The distance of this step.
     *
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var int The duration of this step.
     *
     * @Groups("pt")
     */
    private $duration;
    
    /**
     * @var int The position of this step.
     *
     * @Groups("pt")
     */
    private $position;
    
    /**
     * @var bool The step is the last step of the leg.
     *
     * @Groups("pt")
     */
    private $isLast;
    
    /**
     * @var string The magnetic direction of this step.
     *
     * @Groups("pt")
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this step.
     *
     * @Groups("pt")
     */
    private $relativeDirection;
   
    /**
     * @var PTLeg The parent leg of this step.
     *
     * @Groups("pt")
     */
    private $ptleg;
    
    /**
     * @var PTDeparture The departure of this step.
     *
     * @Groups("pt")
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this step.
     *
     * @Groups("pt")
     */
    private $ptarrival;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPosition($id);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getDistance()
    {
        return $this->distance;
    }
    
    public function setDistance($distance)
    {
        $this->distance = $distance;
    }

    public function getDuration()
    {
        return $this->duration;
    }
    
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getPosition()
    {
        return $this->position;
    }
    
    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function isLast()
    {
        return $this->isLast;
    }
    
    public function setIsLast($isLast)
    {
        $this->isLast = $isLast;
    }
    
    public function getMagneticDirection()
    {
        return $this->magneticDirection;
    }
    
    public function setMagneticDirection($magneticDirection)
    {
        $this->magneticDirection = $magneticDirection;
    }
    
    public function getRelativeDirection()
    {
        return $this->relativeDirection;
    }
    
    public function setRelativeDirection($relativeDirection)
    {
        $this->relativeDirection = $relativeDirection;
    }

    public function getPTLeg()
    {
        return $this->ptleg;
    }
    
    public function setPTLeg($ptleg)
    {
        $this->ptleg = $ptleg;
    }

    public function getPTDeparture()
    {
        return $this->ptdeparture;
    }
    
    public function setPTDeparture($ptdeparture)
    {
        $this->ptdeparture = $ptdeparture;
    }

    public function getPTArrival()
    {
        return $this->ptarrival;
    }

    public function setPTArrival($ptarrival)
    {
        $this->ptarrival = $ptarrival;
    }
}
