<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Entity\MapsAd;

use App\Geography\Entity\Address;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Map's Ad. The necessary data for displaying an Ad on a map
 */
class MapsAd
{
    /**
     * @var Address
     * @Groups({"readCommunityAds"})
    */
    private $origin;

    /**
     * @var Address
     * @Groups({"readCommunityAds"})
    */
    private $destination;

    /**
     * @var int
     * @Groups({"readCommunityAds"})
    */
    private $proposalId;

    /**
     * @var bool
     * @Groups({"readCommunityAds"})
    */
    private $oneWay;

    /**
     * @var bool
     * @Groups({"readCommunityAds"})
    */
    private $regular;

    /**
     * @var \DateTime|null ONLY FOR PUNCTUAL
     * @Groups({"readCommunityAds"})
    */
    private $outwardDate;

    /**
     * @var int The linked entity's id (Community...)
     * @Groups({"readCommunityAds"})
    */
    private $entityId;

    /**
     * @var string
     * @Groups({"readCommunityAds"})
    */
    private $carpoolerFirstName;

    /**
     * @var string
     * @Groups({"readCommunityAds"})
    */
    private $carpoolerLastName;

    public function __construct()
    {
        $this->outwardDate = null;
    }
    
    public function getOrigin(): ?Address
    {
        return $this->origin;
    }

    public function setOrigin(?Address $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): ?Address
    {
        return $this->destination;
    }

    public function setDestination(?Address $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getProposalId(): ?int
    {
        return $this->proposalId;
    }

    public function setProposalId(?int $proposalId): self
    {
        $this->proposalId = $proposalId;

        return $this;
    }

    public function isOneWay(): ?bool
    {
        return $this->oneWay;
    }

    public function setOneWay(?bool $oneWay): self
    {
        $this->oneWay = $oneWay;

        return $this;
    }

    public function isRegular(): ?bool
    {
        return $this->regular;
    }

    public function setRegular(?bool $regular): self
    {
        $this->regular = $regular;

        return $this;
    }

    public function getOutwardDate(): ?\DateTime
    {
        return $this->outwardDate;
    }

    public function setOutwardDate(?\DateTime $outwardDate): self
    {
        $this->outwardDate = $outwardDate;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getCarpoolerFirstName(): ?string
    {
        return $this->carpoolerFirstName;
    }

    public function setCarpoolerFirstName(?string $carpoolerFirstName): self
    {
        $this->carpoolerFirstName = $carpoolerFirstName;

        return $this;
    }

    public function getCarpoolerLastName(): ?string
    {
        return $this->carpoolerLastName;
    }

    public function setCarpoolerLastName(?string $carpoolerLastName): self
    {
        $this->carpoolerLastName = $carpoolerLastName;

        return $this;
    }
}
