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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\RelayPoint\Entity\RelayPoint;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A postal address.
 */
class Address implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int the id of this address
     * @Groups({"post","put"})
     */
    private $id;

    /**
     * @var null|string the iri of this user
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var null|int the layer identified for the address
     *
     * @Groups({"post","put"})
     */
    private $layer;

    /**
     * @var null|string the house number
     *
     * @Groups({"post","put"})
     */
    private $houseNumber;

    /**
     * @var null|string the street
     *
     *@Groups({"post","put"})
     */
    private $street;

    /**
     * @var string the street address
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $streetAddress;

    /**
     * @var null|string the postal code of the address
     *
     * @Groups({"post","put"})
     */
    private $postalCode;

    /**
     * @var null|string the sublocality of the address
     *
     * @Groups({"post","put"})
     */
    private $subLocality;

    /**
     * @var string the locality of the address
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressLocality;

    /**
     * @var null|string the locality admin of the address
     *
     * @Groups({"post","put"})
     */
    private $localAdmin;

    /**
     * @var null|string the county of the address
     *
     * @Groups({"post","put"})
     */
    private $county;

    /**
     * @var null|string the macro county of the address
     *
     * @Groups({"post","put"})
     */
    private $macroCounty;

    /**
     * @var null|string the region of the address
     *
     * @Groups({"post","put"})
     */
    private $region;

    /**
     * @var null|string the macro region of the address
     *
     * @Groups({"post","put"})
     */
    private $macroRegion;

    /**
     * @var string the country of the address
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressCountry;

    /**
     * @var null|string the country code of the address
     *
     * @Groups({"post","put"})
     */
    private $countryCode;

    /**
     * @var null|float the latitude of the address
     *
     * @Groups({"post","put"})
     */
    private $latitude;

    /**
     * @var null|float the longitude of the address
     *
     * @Groups({"post","put"})
     */
    private $longitude;

    /**
     * @var null|int the elevation of the address in metres
     */
    private $elevation;

    /**
     * @var null|string the name of this address
     * @Groups({"post","put"})
     */
    private $name;

    /**
     * @var null|string the venue name of this address
     * @Groups({"post","put"})
     */
    private $venue;

    /**
     * @var null|User the owner of the address
     */
    private $user;

    /**
     * @var bool the address is a home address
     * @Groups({"post","put"})
     */
    private $home;

    /**
     * @var null|array Label for display
     */
    private $displayLabel;

    /**
     * @var null|array the relaypoint related to the address
     */
    private $relayPoint;

    /**
     * @var null|string the icon of the address
     */
    private $icon;

    /**
     * @var null|Event the event of the address
     */
    private $event;

    /**
     * @var null|string the type of the address
     */
    private $type;

    /**
     * @var null|string the region code of the address
     */
    private $regionCode;

    public function __construct()
    {
        $this->userAddresses = new ArrayCollection();
        $this->displayLabel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getLayer(): ?int
    {
        return $this->layer;
    }

    public function setLayer($layer)
    {
        $this->layer = $layer;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber)
    {
        $this->houseNumber = $houseNumber;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street)
    {
        $this->street = $street;
    }

    public function getStreetAddress(): ?string
    {
        if (!$this->streetAddress || '' == $this->streetAddress) {
            return trim($this->houseNumber.' '.$this->street);
        }

        return $this->streetAddress;
    }

    public function setStreetAddress(?string $streetAddress)
    {
        $this->streetAddress = $streetAddress;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function getSubLocality(): ?string
    {
        return $this->subLocality;
    }

    public function setSubLocality(?string $subLocality)
    {
        $this->subLocality = $subLocality;
    }

    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    public function setAddressLocality(?string $addressLocality)
    {
        $this->addressLocality = $addressLocality;
    }

    public function getLocalAdmin(): ?string
    {
        return $this->localAdmin;
    }

    public function setLocalAdmin(?string $localAdmin)
    {
        $this->localAdmin = $localAdmin;
    }

    public function getCounty(): ?string
    {
        return $this->county;
    }

    public function setCounty(?string $county)
    {
        $this->county = $county;
    }

    public function getMacroCounty(): ?string
    {
        return $this->macroCounty;
    }

    public function setMacroCounty(?string $macroCounty)
    {
        $this->macroCounty = $macroCounty;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region)
    {
        $this->region = $region;
    }

    public function getMacroRegion(): ?string
    {
        return $this->macroRegion;
    }

    public function setMacroRegion(?string $macroRegion)
    {
        $this->macroRegion = $macroRegion;
    }

    public function getAddressCountry(): ?string
    {
        return $this->addressCountry;
    }

    public function setAddressCountry(?string $addressCountry)
    {
        $this->addressCountry = $addressCountry;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude)
    {
        $this->longitude = $longitude;
    }

    public function getElevation(): ?int
    {
        return $this->elevation;
    }

    public function setElevation(?int $elevation)
    {
        $this->elevation = $elevation;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function getVenue(): ?string
    {
        return $this->venue;
    }

    public function setVenue(?string $venue)
    {
        $this->venue = $venue;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function isHome(): ?bool
    {
        return $this->home;
    }

    public function setHome(?bool $isHome)
    {
        $this->home = $isHome ? $isHome : false;
    }

    public function getDisplayLabel()
    {
        return $this->displayLabel;
    }

    public function setDisplayLabel(?array $displayLabel)
    {
        $this->displayLabel = $displayLabel;
    }

    public function getRelayPoint(): ?RelayPoint
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?RelayPoint $relayPoint)
    {
        $this->relayPoint = $relayPoint;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon(?string $icon)
    {
        $this->icon = $icon;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(?string $type)
    {
        $this->type = $type;
    }

    public function getRegionCode()
    {
        return $this->regionCode;
    }

    public function setRegionCode(?string $regionCode)
    {
        $this->regionCode = $regionCode;
    }

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
         [
             'id' => $this->getId(),
             'houseNumber' => $this->getHouseNumber(),
             'street' => $this->getStreet(),
             'streetAddress' => $this->getStreetAddress(),
             'postalCode' => $this->getPostalCode(),
             'addressLocality' => $this->getAddressLocality(),
             'name' => $this->getName(),
             'addressCountry' => $this->getAddressCountry(),
             'countryCode' => $this->getCountryCode(),
             'county' => $this->getCounty(),
             'latitude' => $this->getLatitude(),
             'localAdmin' => $this->getLocalAdmin(),
             'longitude' => $this->getLongitude(),
             'macroCounty' => $this->getMacroCounty(),
             'macroRegion' => $this->getMacroRegion(),
             'region' => $this->getRegion(),
             'subLocality' => $this->getSubLocality(),
             'displayLabel' => $this->getDisplayLabel(),
             'home' => $this->isHome(),
             'icon' => $this->getIcon(),
             'venue' => $this->getVenue(),
             'event' => $this->getEvent(),
             'layer' => $this->getLayer(),
             'type' => $this->getType(),
             'regionCode' => $this->getRegionCode(),
             // 'relayPoint'           => $this->getRelayPoint()
         ];
    }
}
