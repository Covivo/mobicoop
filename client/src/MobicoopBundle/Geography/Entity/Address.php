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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;

/**
 * A postal address.
 */
class Address implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this address.
     * @Groups({"post","put"})
     */
    private $id;

    /**
     * @var string|null The iri of this user.
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string|null The house number.
     *
     * @Groups({"post","put"})
     */
    private $houseNumber;

    /**
     * @var string|null The street.
     *
     *@Groups({"post","put"})
     */
    private $street;

    /**
     * @var string The street address.
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $streetAddress;

    /**
     * @var string|null The postal code of the address.
     *
     * @Groups({"post","put"})
     */
    private $postalCode;

    /**
     * @var string|null The sublocality of the address.
     *
     * @Groups({"post","put"})
     */
    private $subLocality;

    /**
     * @var string The locality of the address.
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressLocality;

    /**
     * @var string|null The locality admin of the address.
     *
     * @Groups({"post","put"})
     */
    private $localAdmin;

    /**
     * @var string|null The county of the address.
     *
     * @Groups({"post","put"})
     */
    private $county;

    /**
     * @var string|null The macro county of the address.
     *
     * @Groups({"post","put"})
     */
    private $macroCounty;

    /**
     * @var string|null The region of the address.
     *
     * @Groups({"post","put"})
     */
    private $region;

    /**
     * @var string|null The macro region of the address.
     *
     * @Groups({"post","put"})
     */
    private $macroRegion;

    /**
     * @var string The country of the address.
     *
     * @Assert\NotBlank
     *
     * @Groups({"post","put"})
     */
    private $addressCountry;

    /**
     * @var string|null The country code of the address.
     *
     * @Groups({"post","put"})
     */
    private $countryCode;
    
    /**
     * @var float|null The latitude of the address.
     *
     * @Groups({"post","put"})
     */
    private $latitude;
    
    /**
     * @var float|null The longitude of the address.
     *
     * @Groups({"post","put"})
     */
    private $longitude;
    
    /**
     * @var int|null The elevation of the address in metres.
     */
    private $elevation;
    
    /**
     * @var string|null The name of this address.
     * @Groups({"post","put"})
     */
    private $name;
    
    /**
     * @var string|null The venue name of this address.
     * @Groups({"post","put"})
     */
    private $venue;

    /**
     * @var User|null The owner of the address.
     */
    private $user;

    /**
     * @var boolean The address is a home address.
     * @Groups({"post","put"})
     */
    private $home;

    /**
     * @var array|null Label for display
     */
    private $displayLabel;

    /**
     * @var array|null The relaypoint related to the address.
     */
    private $relayPoint;
    
    /**
     * @var string|null The icon of the address.
     *
     */
    private $icon;

    /**
     * @var Event|null The event of the address.
     *
     */
    private $event;

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
        if (!$this->streetAddress || $this->streetAddress == '') {
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

    public function getRelayPoint(): ?array
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?array $relayPoint)
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

    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
         [
             'id'                   => $this->getId(),
             'houseNumber'          => $this->getHouseNumber(),
             'street'               => $this->getStreet(),
             'streetAddress'        => $this->getStreetAddress(),
             'postalCode'           => $this->getPostalCode(),
             'addressLocality'      => $this->getAddressLocality(),
             'name'                 => $this->getName(),
             'addressCountry'       => $this->getAddressCountry(),
             'countryCode'          => $this->getCountryCode(),
             'county'               => $this->getCounty(),
             'latitude'             => $this->getLatitude(),
             'localAdmin'           => $this->getLocalAdmin(),
             'longitude'            => $this->getLongitude(),
             'macroCounty'          => $this->getMacroCounty(),
             'macroRegion'          => $this->getMacroRegion(),
             'region'               => $this->getRegion(),
             'subLocality'          => $this->getSubLocality(),
             'displayLabel'         => $this->getDisplayLabel(),
             'home'                 => $this->isHome(),
             'icon'                 => $this->getIcon(),
             'venue'                => $this->getVenue(),
             'event'                => $this->getEvent()
         ];
    }
}
