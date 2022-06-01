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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Travel\Entity\TravelMode;

/**
 * A leg of a public transport journey.
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
 *              "path"="/legs/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLeg
{
    /**
     * @var int The id of this leg.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string The indication of this leg.
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups("pt")
     */
    private $indication;

    /**
     * @var int The distance of this leg.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $distance;

    /**
     * @var string The duration of this leg (in seconds).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $duration;

    /**
     * @var int The position of this leg.
     *
     * @ORM\Column(type="integer")
     * @Groups("pt")
     */
    private $position;

    /**
     * @var bool The leg is the last leg of the journey.
     *
     * @ORM\Column(type="boolean")
     * @Groups("pt")
     */
    private $isLast;

    /**
     * @var string The magnetic direction of this leg.
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $magneticDirection;

    /**
     * @var string The relative direction of this leg.
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $relativeDirection;

    /**
     * @var PTJourney The parent journey of this leg.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTJourney", inversedBy="ptlegs")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptjourney;

    /**
     * @var PTDeparture The departure of this leg.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTDeparture")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptdeparture;

    /**
     * @var PTArrival The arrival of this leg.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTArrival")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptarrival;

    /**
     * @var TravelMode The transport mode of this leg.
     *
     * @ORM\ManyToOne(targetEntity="App\Travel\Entity\TravelMode")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $travelMode;

    /**
     * @var PTLine The public transport line of this leg.
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTLine")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptline;

    /**
     * @var string The direction of the public transport line of this leg.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups("pt")
     */
    private $direction;

    /**
     * @var PTStep[] The steps of this leg.
     *
     * @ORM\OneToMany(targetEntity="App\PublicTransport\Entity\PTStep", mappedBy="ptleg", cascade={"persist"})
     * @Groups("pt")
     */
    private $ptsteps;

    public function __construct($id)
    {
        $this->id = $id;
        $this->setPosition($id);
        $this->ptsteps = new ArrayCollection();
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

    public function getIndication(): ?string
    {
        return $this->indication;
    }

    public function setIndication(?string $indication): self
    {
        $this->indication = $indication;

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

    public function getPTJourney(): PTJourney
    {
        return $this->ptjourney;
    }

    public function setPTJourney(?PTJourney $ptjourney): self
    {
        $this->ptjourney = $ptjourney;

        return $this;
    }

    public function getPTDeparture(): ?PTDeparture
    {
        return $this->ptdeparture;
    }

    public function setPTDeparture(?PTDeparture $ptdeparture): self
    {
        $this->ptdeparture = $ptdeparture;

        return $this;
    }

    public function getPTArrival(): ?PTArrival
    {
        return $this->ptarrival;
    }

    public function setPTArrival(?PTArrival $ptarrival): self
    {
        $this->ptarrival = $ptarrival;

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

    public function getPTLine(): ?PTLine
    {
        return $this->ptline;
    }

    public function setPTLine(?PTLine $ptline): self
    {
        $this->ptline = $ptline;

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

    public function getPTSteps()
    {
        return $this->ptsteps->getValues();
    }

    public function setPTSteps(ArrayCollection $ptsteps): self
    {
        $this->ptsteps = $ptsteps;

        return $this;
    }

    public function addPTStep(PTStep $ptstep): self
    {
        if (!$this->ptsteps->contains($ptstep)) {
            $this->ptsteps->add($ptstep);
            $ptstep->setPTLeg($this);
        }

        return $this;
    }

    public function removePTLeg(PTStep $ptstep): self
    {
        if ($this->ptsteps->contains($ptstep)) {
            $this->ptsteps->removeElement($ptstep);
            // set the owning side to null (unless already changed)
            if ($ptstep->getPTLeg() === $this) {
                $ptstep->setPTLeg(null);
            }
        }

        return $this;
    }
}
