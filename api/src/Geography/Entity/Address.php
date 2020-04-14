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

namespace App\Geography\Entity;

use App\Community\Entity\Community;
use App\Event\Entity\Event;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use App\Carpool\Entity\WayPoint;
use App\User\Entity\User;
use App\Image\Entity\Icon;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use App\Geography\Controller\AddressSearch;
use App\RelayPoint\Entity\RelayPoint;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A postal address (including textual informations and / or geometric coordinates).
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read","pt","mass","search"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "search"={
 *              "method"="GET",
 *              "path"="/addresses/search",
 *              "swagger_context"={
 *                  "parameters"={
 *                     {
 *                         "name" = "q",
 *                         "in" = "query",
 *                         "required" = "true",
 *                         "type" = "string",
 *                         "description" = "The query"
 *                     },
 *                     {
 *                         "name" = "token",
 *                         "in" = "query",
 *                         "type" = "string",
 *                         "description" = "The geographic token authorization"
 *                     }
 *                  }
 *              }
 *          },
 *          "reverse"={
 *              "method"="GET",
 *              "path"="/addresses/reverse",
 *              "swagger_context"={
 *                  "parameters"={
 *                     {
 *                         "name" = "latitude",
 *                         "in" = "query",
 *                         "required" = "true",
 *                         "type" = "string",
 *                         "description" = "Latitude of the point"
 *                     },
 *                     {
 *                         "name" = "longitude",
 *                         "in" = "query",
 *                         "type" = "string",
 *                         "description" = "Longitude of the point"
 *                     }
 *                   }
 *              }
 *          }
 *      },
 *      itemOperations={"get","put"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "streetAddress", "postalCode", "addressLocality", "addressCountry"}, arguments={"orderParameterName"="order"})
 */

class Address implements \JsonSerializable
{
    const DEFAULT_ID = 999999999999;
    const HOME_ADDRESS = "homeAddress";

    /**
     * @var int The id of this address.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read", "readUser", "readEvent"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string The house number.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $houseNumber;

    /**
     * @var string The street.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute"})
     * @Assert\NotBlank(groups={"mass","massCompute","threads","thread"})
     */
    private $street;

    /**
     * @var string The full street address.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread","externalJourney"})
     */
    private $streetAddress;

    /**
     * @var string|null The postal code of the address.
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","externalJourney"})
     * @Assert\NotBlank(groups={"mass","massCompute","threads","thread"})
     */
    private $postalCode;

    /**
     * @var string|null The sublocality of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $subLocality;

    /**
     * @var string|null The locality of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","readEvent","results","write","pt","mass","massCompute","threads","thread","externalJourney","readCommunity"})
     * @Assert\NotBlank(groups={"mass","massCompute","threads","thread"})
     */
    private $addressLocality;

    /**
     * @var string|null The locality admin of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $localAdmin;

    /**
     * @var string|null The county of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $county;

    /**
     * @var string|null The macro county of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $macroCounty;

    /**
     * @var string|null The region of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $region;

    /**
     * @var string|null The macro region of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $macroRegion;

    /**
     * @var string|null The country of the address.
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread","externalJourney"})
     */
    private $addressCountry;

    /**
     * @var string|null The country code of the address.
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"read","readUser","results","write","pt","mass","massCompute","threads","thread"})
     */
    private $countryCode;

    /**
     * @var float|null The latitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","readUser","readCommunity","readEvent","results","write","pt","mass","massCompute","threads","thread","externalJourney"})
     */
    private $latitude;

    /**
     * @var float|null The longitude of the address.
     *
     * @ORM\Column(type="decimal", precision=10, scale=6, nullable=true)
     * @Groups({"read","readUser","readCommunity","readEvent","results","write","pt","mass","massCompute","threads","thread","externalJourney"})
     */
    private $longitude;

    /**
     * @var int|null The elevation of the address in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","results","write","pt","mass","massCompute"})
     */
    private $elevation;

    /**
     * @var string The geoJson point of the address.
     * @ORM\Column(type="point", nullable=true)
     * @Groups({"read","write","readEvent"})
     */
    private $geoJson;

    /**
     * @var string|null The name of this address.
     *
     * @ORM\Column(type="string", length=45, nullable=true)
     * @Groups({"read","readUser","results","write"})
     */
    private $name;

    /**
     * @var string|null The venue name of this address.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"read","readUser","results","write"})
     */
    private $venue;

    /**
     * @var User|null The owner of the address.
     *
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="addresses")
     */
    private $user;

    /**
     * @var boolean The address is a home address.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","readUser","results","write"})
     */
    private $home;

    /**
     * @var array|null Label for display
     *
     * @Groups({"read","readUser","readCommunity","readEvent","results","pt"})
     */
    private $displayLabel;

    /**
     * @var RelayPoint|null The relaypoint related to the address.
     * @Groups({"read","pt"})
     */
    private $relayPoint;

    /**
     * @var Event|null The event of the address.
     *
     * @ORM\OneToOne(targetEntity="App\Event\Entity\Event", mappedBy="address")
     * @Groups({"read","pt","readEvent","write"})
     */
    private $event;

    /**
     * @var Community|null The community of the address.
     *
     * @ORM\OneToOne(targetEntity="App\Community\Entity\Community", mappedBy="address")
     * @Groups({"read"})
     */
    private $community;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    /**
     * @var ArrayCollection|null The territories of this address.
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"read","write"})
     */
    private $territories;

    /**
     * @var string|null Icon fileName.
     *
     * @Groups({"read"})
     */
    private $icon;

    /**
     * @var array|null The provider of the address
     *
     * @Groups({"read"})
     */
    private $providedBy;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->displayLabel = new ArrayCollection();
        $this->territories = new ArrayCollection();
    }

    public function __clone()
    {
        // when we clone an Address we exclude the id
        $this->id = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
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

    public function getGeoJson()
    {
        return $this->geoJson;
    }
    
    public function setGeoJson($geoJson): self
    {
        $this->geoJson = $geoJson;
        
        return $this;
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
    
    public function setHome(?bool $isHome): self
    {
        $this->home = $isHome;
        
        return $this;
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon)
    {
        $this->icon = $icon;
    }

    public function getProvidedBy(): ?string
    {
        return $this->providedBy;
    }

    public function setProvidedBy(?string $providedBy)
    {
        $this->providedBy = $providedBy;
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

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setCommunity(?Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function getTerritories()
    {
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories[] = $territory;
        }
        
        return $this;
    }
    
    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }
        return $this;
    }

    public function removeTerritories(): self
    {
        $this->territories->clear();
        return $this;
    }


    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }
    
    /**
     * GeoJson representation.
     *
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setAutoGeoJson()
    {
        if (!is_null($this->getLatitude()) && !is_null($this->getLongitude())) {
            $this->setGeoJson(new Point($this->getLongitude(), $this->getLatitude()));
        }
    }

    
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
