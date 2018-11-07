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
use App\Address\Entity\Address;

/**
 * A public transport step (by walk or public transport).
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
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
    private $pos;
    
    /**
     * @var bool The step is the last step of the section.
     *
     * @Groups("pt")
     */
    private $last;
    
    /**
     * @var string The magnetic direction of this section.
     *
     * @Groups("pt")
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this section.
     *
     * @Groups("pt")
     */
    private $relativeDirection;
   
    /**
     * @var PTSection The parent section of this step.
     *
     * @Groups("pt")
     */
    private $ptsection;
    
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
        $this->setPos($id);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getDistance ()
    {
        return $this->distance;
    }

    public function getDuration ()
    {
        return $this->duration;
    }

    public function getPos ()
    {
        return $this->pos;
    }

    public function isLast ()
    {
        return $this->last;
    }

    public function getPTSection ()
    {
        return $this->ptsection;
    }

    public function getPTDeparture ()
    {
        return $this->ptdeparture;
    }

    public function getPTArrival ()
    {
        return $this->ptarrival;
    }

    public function setDistance ($distance)
    {
        $this->distance = $distance;
    }

    public function setDuration ($duration)
    {
        $this->duration = $duration;
    }

    public function setPos ($pos)
    {
        $this->pos = $pos;
    }

    public function setLast ($last)
    {
        $this->last = $last;
    }

    public function setPTSection ($ptsection)
    {
        $this->ptsection = $ptsection;
    }

    public function setPTDeparture ($ptdeparture)
    {
        $this->ptdeparture = $ptdeparture;
    }

    public function setPTArrival ($ptarrival)
    {
        $this->ptarrival = $ptarrival;
    }

    public function getMagneticDirection ()
    {
        return $this->magneticDirection;
    }
    
    public function getRelativeDirection ()
    {
        return $this->relativeDirection;
    }
    
    public function setMagneticDirection ($magneticDirection)
    {
        $this->magneticDirection = $magneticDirection;
    }
    
    public function setRelativeDirection ($relativeDirection)
    {
        $this->relativeDirection = $relativeDirection;
    }
}
