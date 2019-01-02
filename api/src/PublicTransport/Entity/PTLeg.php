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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A leg of a journey.
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"={"path"="/legs/{id}"}}
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLeg
{
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The indication of this leg.
     *
     * @Groups("pt")
     */
    private $indication;
    
    /**
     * @var int The distance of this leg.
     *
     * @Groups("pt")
     */
    private $distance;
    
    /**
     * @var int The duration of this leg.
     *
     * @Groups("pt")
     */
    private $duration;
    
    /**
     * @var int The position of this leg.
     *
     * @Groups("pt")
     */
    private $position;
    
    /**
     * @var bool The leg is the last leg of the journey.
     *
     * @Groups("pt")
     */
    private $isLast;
    
    /**
     * @var string The magnetic direction of this leg.
     *
     * @Groups("pt")
     */
    private $magneticDirection;
    
    /**
     * @var string The relative direction of this leg.
     *
     * @Groups("pt")
     */
    private $relativeDirection;
    
    /**
     * @var PTJourney The parent journey of this leg.
     *
     * @Groups("pt")
     */
    private $ptjourney;
    
    /**
     * @var PTDeparture The departure of this leg.
     *
     * @Groups("pt")
     */
    private $ptdeparture;
    
    /**
     * @var PTArrival The arrival of this leg.
     *
     * @Groups("pt")
     */
    private $ptarrival;
    
    /**
     * @var PTMode The transport mode of this leg.
     *
     * @Groups("pt")
     */
    private $ptmode;
    
    /**
     * @var PTLine The public transport line of this leg.
     *
     * @Groups("pt")
     */
    private $ptline;
    
    /**
     * @var string The direction of the public transport line of this leg.
     *
     * @Groups("pt")
     */
    private $direction;
    
    /**
     * @var PTStep[] The steps of this leg.
     *
     * @Groups("pt")
     */
    private $ptsteps;
    
    public function __construct($id)
    {
        $this->id = $id;
        $this->setPosition($id);
        $this->ptsteps = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getIndication()
    {
        return $this->indication;
    }
    
    public function setIndication($indication)
    {
        $this->indication = $indication;
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

    public function getPTJourney()
    {
        return $this->ptjourney;
    }
    
    public function setPTJourney($ptjourney)
    {
        $this->ptjourney = $ptjourney;
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

    public function getPTMode()
    {
        return $this->ptmode;
    }
    
    public function setPTMode($ptmode)
    {
        $this->ptmode = $ptmode;
    }

    public function getPTLine()
    {
        return $this->ptline;
    }
    
    public function setPTLine($ptline)
    {
        $this->ptline = $ptline;
    }

    public function getDirection()
    {
        return $this->direction;
    }
    
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
    
    public function getPTSteps()
    {
        return $this->ptsteps;
    }

    public function setPTSteps($ptsteps)
    {
        $this->ptsteps = $ptsteps;
    }
}
