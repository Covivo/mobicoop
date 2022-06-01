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
use App\Geography\Entity\Address;
use Doctrine\ORM\Mapping as ORM;

/**
 * An accessibility status for public transport lines.
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *     },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Public Transport"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTAccessibilityStatus
{
    /**
     * @ApiProperty(identifier=true)
     * @var int id of this accessibility status
     * @Groups("pt")
     */
    private $id;

    /**
     * @var int blind Accessibility
     * @Groups("pt")
     */
    private $blindAccess;

    /**
     * @var int deaf Accessibility
     * @Groups("pt")
     */
    private $deafAccess;

    /**
     * @var int mental illness Accessibility
     * @Groups("pt")
     */
    private $mentalIllnessAccess;

    /**
     * @var int wheelchair Accessibility
     * @Groups("pt")
     */
    private $wheelChairAccess;

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

    public function getBlindAccess(): int
    {
        return $this->blindAccess;
    }

    public function setBlindAccess(int $blindAccess): self
    {
        $this->blindAccess = $blindAccess;

        return $this;
    }

    public function getDeafAccess(): int
    {
        return $this->deafAccess;
    }

    public function setDeafAccess(int $deafAccess): self
    {
        $this->deafAccess = $deafAccess;

        return $this;
    }

    public function getMentalIllnessAccess(): int
    {
        return $this->mentalIllnessAccess;
    }

    public function setMentalIllnessAccess(int $mentalIllnessAccess): self
    {
        $this->mentalIllnessAccess = $mentalIllnessAccess;

        return $this;
    }

    public function getWheelChairAccess(): int
    {
        return $this->wheelChairAccess;
    }

    public function setWheelChairAccess(int $wheelChairAccess): self
    {
        $this->wheelChairAccess = $wheelChairAccess;

        return $this;
    }
}
