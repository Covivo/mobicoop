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
 */

namespace App\PublicTransport\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport step (by walk or public transport).
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
 *              "path"="/steps/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTStep
{
    /**
     * @var int the id of this step
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int the distance of this step
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distance;

    /**
     * @var string the duration of this step (in seconds)
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $duration;

    /**
     * @var int the position of this step
     *
     * @ORM\Column(type="integer")
     * @Groups("pt")
     */
    private $position;

    /**
     * @var bool the step is the last step of the leg
     *
     * @ORM\Column(type="boolean")
     * @Groups("pt")
     */
    private $isLast;

    /**
     * @var string the magnetic direction of this step
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $magneticDirection;

    /**
     * @var string the relative direction of this step
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $relativeDirection;

    /**
     * @var PTLeg the parent leg of this step
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTLeg", inversedBy="ptsteps")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptleg;

    /**
     * @var PTDeparture the departure of this step
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTDeparture")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptdeparture;

    /**
     * @var PTArrival the arrival of this step
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTArrival")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptarrival;

    /**
     * @var string Geometric path of this step
     * @Groups("pt")
     */
    private $geometry;

    public function __construct($id)
    {
        $this->id = $id;
        $this->setPosition($id);
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

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isLast(): bool
    {
        return $this->isLast;
    }

    public function setIsLast(bool $isLast): self
    {
        $this->isLast = $isLast;

        return $this;
    }

    public function getMagneticDirection(): ?string
    {
        return $this->magneticDirection;
    }

    public function setMagneticDirection(?string $magneticDirection): self
    {
        $this->magneticDirection = $magneticDirection;

        return $this;
    }

    public function getRelativeDirection(): ?string
    {
        return $this->relativeDirection;
    }

    public function setRelativeDirection(?string $relativeDirection): self
    {
        $this->relativeDirection = $relativeDirection;

        return $this;
    }

    public function getPTLeg(): PTLeg
    {
        return $this->ptleg;
    }

    public function setPTLeg(?PTLeg $ptleg): self
    {
        $this->ptleg = $ptleg;

        return $this;
    }

    public function getPTDeparture(): PTDeparture
    {
        return $this->ptdeparture;
    }

    public function setPTDeparture(PTDeparture $ptdeparture): self
    {
        $this->ptdeparture = $ptdeparture;

        return $this;
    }

    public function getPTArrival(): PTArrival
    {
        return $this->ptarrival;
    }

    public function setPTArrival(PTArrival $ptarrival): self
    {
        $this->ptarrival = $ptarrival;

        return $this;
    }

    public function getGeometry(): ?string
    {
        return $this->geometry;
    }

    public function setGeometry(string $geometry): self
    {
        $this->geometry = $geometry;

        return $this;
    }
}
