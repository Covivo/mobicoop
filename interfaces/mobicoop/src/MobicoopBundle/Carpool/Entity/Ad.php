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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;

/**
 * Carpooling : an ad on the platform (offer from a driver / request from a passenger).
 * This entity is used to simplify the process and give all the requested fields on one entity, instead of creating nested forms.
 * Therefor it's NOT a table in the database.
 */
class Ad
{
    const ROLE_DRIVER = 1;
    const ROLE_PASSENGER = 2;
    const ROLE_BOTH = 3;

    const ROLES = [
        "ad.role.choice.driver"=>self::ROLE_DRIVER,
        "ad.role.choice.passenger"=>self::ROLE_PASSENGER,
        "ad.role.choice.both"=>self::ROLE_BOTH
    ];

    const TYPE_ONE_WAY = 1;
    const TYPE_RETURN_TRIP = 2;

    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;

    const FREQUENCIES = [
        "ad.frequency.choice.punctual"=>self::FREQUENCY_PUNCTUAL,
        "ad.frequency.choice.regular"=>self::FREQUENCY_REGULAR
    ];
    
    const PRICE = 1.10;
    
    const MARGIN_TIME = [0=>0,5=>5,10=>10,15=>15,30=>30,45=>45,60=>60];

    
    /*
     * Properties for fields that will be used in the form
     */ 
    
    /**
     * @var string The origin of the travel.
     *
     * @Assert\NotBlank
     */
    private $origin;

    /**
     * @var string The destination of the travel.
     *
     * @Assert\NotBlank
     */
    private $destination;
    
    /**
     * @var int The ad role (driver / passenger / both).
     *
     * @Assert\NotBlank
     */
    private $role;
    
    /**
     * @var \DateTimeInterface Date of the outward travel if punctual.
     */
    private $outwardDate;
    
    /**
     * @var int Margin time of the outward travel if punctual.
     */
    private $outwardMargin;

    /**
     * @var \DateTimeInterface Date of the return travel if punctual.
     */
    private $returnDate;
    
    /**
     * @var int Margin time of the return travel if punctual.
     */
    private $returnMargin;
    
    /**
     * @var array Dates of the travels if regular.
     * format :
     * [
     *      'monday' => [
     *          'O' => [
     *              'date'      => outward_date,
     *              'margin'    => margin_time
     *          ],
     *          'R' => [
     *              'date'      => return_date,
     *              'margin'    => margin_time
     *          ],
     *      ],
     *      ...
     * ]
     */
    private $regularDates;
    
    /**
    * @var int The ad type (one way / return trip).
     *
     * @Assert\NotBlank
    */
    private $type;

    /**
     * @var int The frequency of the ad (punctual / regular).
     *
     * @Assert\NotBlank
     */
    private $frequency;

    /**
     * @var string The comment of the ad.
     */
    private $comment;
    
    /**
     * @var float The km price of the ad.
     */
    private $price;
    
    
    /*
     * Properties that will be needed to create the proposal from the ad
     */
    
    /**
     * @var User The user who submits the ad.
     */
    private $user;

    /**
     * @var string The latitude of the origin of the travel.
     */
    private $originLatitude;

    /**
     * @var string The longitude of the origin of the travel.
     */
    private $originLongitude;

    /**
     * @var string The latitude of the destination of the travel.
     */
    private $destinationLatitude;

    /**
     * @var string The longitude of the destination of the travel.
     */
    private $destinationLongitude;
    
    private $originAddress;
    private $destinationAddress;

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

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }
    
    public function getOutwardDate(): ?\DateTimeInterface
    {
        return $this->outwardDate;
    }
    
    public function setOutwardDate(?\DateTimeInterface $outwardDate): self
    {
        $this->outwardDate = $outwardDate;
        
        return $this;
    }
    
    public function getOutwardMargin(): ?int
    {
        return $this->outwardMargin;
    }
    
    public function setOutwardMargin(int $outwardMargin): self
    {
        $this->outwardMargin = $outwardMargin;
        
        return $this;
    }
    
    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }
    
    public function setReturnDate(?\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;
        
        return $this;
    }
    
    public function getReturnMargin(): ?int
    {
        return $this->returnMargin;
    }
    
    public function setReturnMargin(int $returnMargin): self
    {
        $this->returnMargin = $returnMargin;
        
        return $this;
    }
    
    public function getRegularDates(): ?array
    {
        return $this->regularDates;
    }
    
    public function setRegularDates(?array $regularDates): self
    {
        $this->regularDates = $regularDates;
        
        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }
    
    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        
        return $this;
    }
    
    public function getPrice(): ?float
    {
        return $this->price;
    }
    
    public function setPrice(?float $price): self
    {
        $this->price = $price;
        
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser(?User $user): self
    {
        $this->user = $user;
        
        return $this;
    }

    public function getOriginLatitude(): ?float
    {
        return $this->originLatitude;
    }

    public function setOriginLatitude(float $originLatitude): self
    {
        $this->originLatitude = $originLatitude;

        return $this;
    }

    public function getOriginLongitude(): ?float
    {
        return $this->originLongitude;
    }

    public function setOriginLongitude(float $originLongitude): self
    {
        $this->originLongitude = $originLongitude;

        return $this;
    }

    public function getDestinationLatitude(): ?float
    {
        return $this->destinationLatitude;
    }

    public function setDestinationLatitude(float $destinationLatitude): self
    {
        $this->destinationLatitude = $destinationLatitude;

        return $this;
    }

    public function getDestinationLongitude(): ?float
    {
        return $this->destinationLongitude;
    }

    public function setDestinationLongitude(float $destinationLongitude): self
    {
        $this->destinationLongitude = $destinationLongitude;

        return $this;
    }
    
    public function getOriginAddress(): ?Address
    {
        return $this->originAddress;
    }
    
    public function setOriginAddress(?Address $originAddress): self
    {
        $this->originAddress = $originAddress;
        
        return $this;
    }
    
    public function getDestinationAddress(): ?Address
    {
        return $this->destinationAddress;
    }
    
    public function setDestinationAddress(?Address $destinationAddress): self
    {
        $this->destinationAddress = $destinationAddress;
        
        return $this;
    }
}
