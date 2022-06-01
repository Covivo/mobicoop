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
use App\Travel\Entity\TravelMode;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport line.
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
 *              "path"="/lines/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTLine
{
    /**
     * @var int the id of this line
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string the name of this line
     *
     * @ORM\Column(type="string", length=45)
     * @Groups("pt")
     */
    private $name;

    /**
     * @var string the number of this line
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $number;

    /**
     * @var string the origin of this line
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("pt")
     */
    private $origin;

    /**
     * @var string the destination of this line
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("pt")
     */
    private $destination;

    /**
     * @var string the direction of this line if no origin / destination specified
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("pt")
     */
    private $direction;

    /**
     * @var PTCompany the company that manage this line
     *
     * @ORM\ManyToOne(targetEntity="App\PublicTransport\Entity\PTCompany")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $ptcompany;

    /**
     * @var null|TravelMode the transport mode of this line
     *
     * @ORM\ManyToOne(targetEntity="App\Travel\Entity\TravelMode")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("pt")
     */
    private $travelMode;

    /**
     * @var int the transport mode of this line
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("pt")
     */
    private $transportMode;

    /**
     * @var string the color of this line
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("pt")
     */
    private $color;

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

    public function getTravelMode(): ?TravelMode
    {
        return $this->travelMode;
    }

    public function setTravelMode(TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;

        return $this;
    }

    public function getTransportMode(): ?int
    {
        return $this->transportMode;
    }

    public function setTransportMode(int $transportMode): self
    {
        $this->transportMode = $transportMode;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;

        return $this;
    }
}
