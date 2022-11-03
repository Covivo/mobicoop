<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * A relay point map.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
class RelayPointMap implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this relay point map
     */
    private $id;

    /**
     * @var string The name of this relay point map
     */
    private $name;

    /**
     * @var RelayPointType the relay point type of the relay point map
     */
    private $relayPointType;

    /**
     * @var Address the address of the relay point map
     */
    private $address;

    /**
     * @var null|int the number of places
     */
    private $places;

    /**
     * @var null|int the number of places for disabled people
     */
    private $placesDisabled;

    /**
     * @var null|bool the relay point is free
     */
    private $free;

    /**
     * @var null|bool the relay point is secured
     */
    private $secured;

    /**
     * @var null|bool the relay point is official
     */
    private $official;

    /**
     * @var null|bool the relay point is private to a community or a solidary structure
     */
    private $private;

    /**
     * @var null|string Image of the RelayPointMap
     */
    private $image;

    /**
     * @var null|string the description of the relay point
     */
    private $description;

    public function __construct($id = null)
    {
        if ($id) {
            $this->setId($id);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getRelayPointType(): ?RelayPointType
    {
        return $this->relayPointType;
    }

    public function setRelayPointType(?RelayPointType $relayPointType): self
    {
        $this->relayPointType = $relayPointType;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $isPrivate): self
    {
        $this->private = $isPrivate;

        return $this;
    }

    public function getPlaces()
    {
        return $this->places;
    }

    public function setPlaces(?int $places)
    {
        $this->places = $places;
    }

    public function getPlacesDisabled()
    {
        return $this->placesDisabled;
    }

    public function setPlacesDisabled(?int $placesDisabled)
    {
        $this->placesDisabled = $placesDisabled;
    }

    public function isFree(): ?bool
    {
        return $this->free;
    }

    public function setFree(?bool $isFree): self
    {
        $this->free = $isFree;

        return $this;
    }

    public function isSecured(): ?bool
    {
        return $this->secured;
    }

    public function setSecured(?bool $isSecured): self
    {
        $this->secured = $isSecured;

        return $this;
    }

    public function isOfficial(): ?bool
    {
        return $this->official;
    }

    public function setOfficial(?bool $isOfficial): self
    {
        $this->official = $isOfficial;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'address' => $this->getAddress(),
            'relayPointType' => $this->getRelayPointType(),
            'private' => $this->isPrivate(),
            'places' => $this->getPlaces(),
            'placesDisabled' => $this->getPlacesDisabled(),
            'free' => $this->isFree(),
            'secured' => $this->isSecured(),
            'official' => $this->isOfficial(),
            'image' => $this->getImage(),
            'description' => $this->getDescription(),
        ];
    }
}
