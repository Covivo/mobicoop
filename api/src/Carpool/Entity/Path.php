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

namespace App\Carpool\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel path between 2 points.
 *
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class Path
{
    /**
     * @var int The id of this path.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Position number of the current part in the whole path.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $position;

    /**
     * @var string Path detail.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"read"})
     */
    private $detail;

    /**
     * @var int Encoding format (1 = json; 2 = xml)
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read"})
     */
    private $encodeFormat;

    /**
     * @var Point The starting point of the path.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Point", inversedBy="pathStart")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $point1;

    /**
     * @var Point The destination point of the path.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Point", inversedBy="pathDestination")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $point2;

    /**
     * @var TravelMode The travel mode of the path.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\TravelMode")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $travelMode;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getEncodeFormat(): ?int
    {
        return $this->encodeFormat;
    }

    public function setEncodeFormat(int $encodeFormat): self
    {
        $this->encodeFormat = $encodeFormat;

        return $this;
    }

    public function getPoint1(): ?Point
    {
        return $this->point1;
    }

    public function setPoint1(?Point $point1): self
    {
        $this->point1 = $point1;

        return $this;
    }

    public function getPoint2(): ?Point
    {
        return $this->point2;
    }

    public function setPoint2(?Point $point2): self
    {
        $this->point2 = $point2;

        return $this;
    }

    public function getTravelMode(): ?TravelMode
    {
        return $this->travelMode;
    }

    public function setTravelMode(?TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;

        return $this;
    }
}
