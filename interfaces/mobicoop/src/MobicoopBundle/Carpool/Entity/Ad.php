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

use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

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

    const TYPES = [
        "ad.type.choice.oneway"=>self::TYPE_ONE_WAY,
        "ad.type.choice.return"=>self::TYPE_RETURN_TRIP
    ];

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
    

    // PUNCTUAL

    /**
     * @var string Date of the outward travel if punctual (in string format as we use a datepicker).
     */
    private $outwardDate;

    /**
     * @var string Time of the outward travel if punctual (in string format as we use a datepicker).
     */
    private $outwardTime;
    
    /**
     * @var int Margin time of the outward travel if punctual.
     */
    private $outwardMargin;

    /**
     * @var string Date of the return travel if punctual (in string format as we use a datepicker).
     */
    private $returnDate;

    /**
     * @var string Time of the return travel if punctual (in string format as we use a datepicker).
     */
    private $returnTime;
    
    /**
     * @var int Margin time of the return travel if punctual.
     */
    private $returnMargin;
    

    // REGULAR

    /**
     * @var string Date of the first travel if regular.
     */
    private $fromDate;

    /**
     * @var string Date of the last travel if regular.
     */
    private $toDate;

    /**
     * @var bool Outward travel is done on mondays .
     */
    private $outwardMon;

    /**
     * @var string Time of the outward travel on mondays .
     */
    private $outwardMonTime;
    
    /**
     * @var int Margin time of the outward travel on mondays.
     */
    private $outwardMonMargin;

    /**
     * @var bool Return travel is done on mondays .
     */
    private $returnMon;

    /**
     * @var string Time of the return travel on mondays.
     */
    private $returnMonTime;
    
    /**
     * @var int Margin time of the return travel on mondays.
     */
    private $returnMonMargin;

    /**
     * @var bool Outward travel is done on tuesdays .
     */
    private $outwardTue;

    /**
     * @var string Time of the outward travel on tuesdays .
     */
    private $outwardTueTime;
    
    /**
     * @var int Margin time of the outward travel on tuesdays.
     */
    private $outwardTueMargin;

    /**
     * @var bool Return travel is done on tuesdays .
     */
    private $returnTue;

    /**
     * @var string Time of the return travel on tuesdays.
     */
    private $returnTueTime;
    
    /**
     * @var int Margin time of the return travel on tuesdays.
     */
    private $returnTueMargin;

    /**
     * @var bool Outward travel is done on wednesdays .
     */
    private $outwardWed;

    /**
     * @var string Time of the outward travel on wednesdays .
     */
    private $outwardWedTime;
    
    /**
     * @var int Margin time of the outward travel on wednesdays.
     */
    private $outwardWedMargin;

    /**
     * @var bool Return travel is done on wednesdays .
     */
    private $returnWed;

    /**
     * @var string Time of the return travel on wednesdays.
     */
    private $returnWedTime;
    
    /**
     * @var int Margin time of the return travel on wednesdays.
     */
    private $returnWedMargin;

    /**
     * @var bool Outward travel is done on thursdays .
     */
    private $outwardThu;

    /**
     * @var string Time of the outward travel on thursdays .
     */
    private $outwardThuTime;
    
    /**
     * @var int Margin time of the outward travel on thursdays.
     */
    private $outwardThuMargin;

    /**
     * @var bool Return travel is done on thursdays .
     */
    private $returnThu;

    /**
     * @var string Time of the return travel on thursdays.
     */
    private $returnThuTime;
    
    /**
     * @var int Margin time of the return travel on thursdays.
     */
    private $returnThuMargin;

    /**
     * @var bool Outward travel is done on fridays .
     */
    private $outwardFri;

    /**
     * @var string Time of the outward travel on fridays .
     */
    private $outwardFriTime;
    
    /**
     * @var int Margin time of the outward travel on fridays.
     */
    private $outwardFriMargin;

    /**
     * @var bool Return travel is done on fridays .
     */
    private $returnFri;

    /**
     * @var string Time of the return travel on fridays.
     */
    private $returnFriTime;
    
    /**
     * @var int Margin time of the return travel on fridays.
     */
    private $returnFriMargin;

    /**
     * @var bool Outward travel is done on saturdays .
     */
    private $outwardSat;

    /**
     * @var string Time of the outward travel on saturdays .
     */
    private $outwardSatTime;
    
    /**
     * @var int Margin time of the outward travel on saturdays.
     */
    private $outwardSatMargin;

    /**
     * @var bool Return travel is done on saturdays .
     */
    private $returnSat;

    /**
     * @var string Time of the return travel on saturdays.
     */
    private $returnSatTime;
    
    /**
     * @var int Margin time of the return travel on saturdays.
     */
    private $returnSatMargin;

    /**
     * @var bool Outward travel is done on sundays .
     */
    private $outwardSun;

    /**
     * @var string Time of the outward travel on sundays .
     */
    private $outwardSunTime;
    
    /**
     * @var int Margin time of the outward travel on sundays.
     */
    private $outwardSunMargin;

    /**
     * @var bool Return travel is done on sundays .
     */
    private $returnSun;

    /**
     * @var string Time of the return travel on sundays.
     */
    private $returnSunTime;
    
    /**
     * @var int Margin time of the return travel on sundays.
     */
    private $returnSunMargin;
    
    
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
     * @var float The latitude of the origin of the travel.
     */
    private $originLatitude;

    /**
     * @var float The longitude of the origin of the travel.
     */
    private $originLongitude;

    /**
     * @var float The latitude of the destination of the travel.
     */
    private $destinationLatitude;

    /**
     * @var float The longitude of the destination of the travel.
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

    // PUNCTUAL
    
    public function getOutwardDate(): ?string
    {
        return $this->outwardDate;
    }
    
    public function setOutwardDate(?string $outwardDate): self
    {
        $this->outwardDate = $outwardDate;
        
        return $this;
    }

    public function getOutwardTime(): ?string
    {
        return $this->outwardTime;
    }
    
    public function setOutwardTime(?string $outwardTime): self
    {
        $this->outwardTime = $outwardTime;
        
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
    
    public function getReturnDate(): ?string
    {
        return $this->returnDate;
    }
    
    public function setReturnDate(?string $returnDate): self
    {
        $this->returnDate = $returnDate;
        
        return $this;
    }

    public function getReturnTime(): ?string
    {
        return $this->returnTime;
    }
    
    public function setReturnTime(?string $returnTime): self
    {
        $this->returnTime = $returnTime;
        
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

    // REGULAR

    public function getFromDate(): ?string
    {
        return $this->fromDate;
    }
    
    public function setFromDate(?string $fromDate): self
    {
        $this->fromDate = $fromDate;
        
        return $this;
    }

    public function getToDate(): ?string
    {
        return $this->toDate;
    }
    
    public function setToDate(?string $toDate): self
    {
        $this->toDate = $toDate;
        
        return $this;
    }

    public function getOutwardMon(): ?bool
    {
        return $this->outwardMon;
    }
    
    public function setOutwardMon(?bool $outwardMon): self
    {
        $this->outwardMon = $outwardMon;
        
        return $this;
    }
    
    public function getOutwardMonTime(): ?string
    {
        return $this->outwardMonTime;
    }
    
    public function setOutwardMonTime(?string $outwardMonTime): self
    {
        $this->outwardMonTime = $outwardMonTime;
        
        return $this;
    }

    public function getOutwardMonMargin(): ?int
    {
        return $this->outwardMonMargin;
    }
    
    public function setOutwardMonMargin(?string $outwardMonMargin): self
    {
        $this->outwardMonMargin = $outwardMonMargin;
        
        return $this;
    }

    public function getReturnMon(): ?bool
    {
        return $this->returnMon;
    }
    
    public function setReturnMon(?bool $returnMon): self
    {
        $this->returnMon = $returnMon;
        
        return $this;
    }
    
    public function getReturnMonTime(): ?string
    {
        return $this->returnMonTime;
    }
    
    public function setReturnMonTime(?string $returnMonTime): self
    {
        $this->returnMonTime = $returnMonTime;
        
        return $this;
    }

    public function getReturnMonMargin(): ?int
    {
        return $this->returnMonMargin;
    }
    
    public function setReturnMonMargin(?string $returnMonMargin): self
    {
        $this->oreturnMonMargin = $returnMonMargin;
        
        return $this;
    }
    
    public function getOutwardTue(): ?bool
    {
        return $this->outwardTue;
    }
    
    public function setOutwardTue(?bool $outwardTue): self
    {
        $this->outwardTue = $outwardTue;
        
        return $this;
    }
    
    public function getOutwardTueTime(): ?string
    {
        return $this->outwardTueTime;
    }
    
    public function setOutwardTueTime(?string $outwardTueTime): self
    {
        $this->outwardTueTime = $outwardTueTime;
        
        return $this;
    }

    public function getOutwardTueMargin(): ?int
    {
        return $this->outwardTueMargin;
    }
    
    public function setOutwardTueMargin(?string $outwardTueMargin): self
    {
        $this->outwardTueMargin = $outwardTueMargin;
        
        return $this;
    }

    public function getReturnTue(): ?bool
    {
        return $this->returnTue;
    }
    
    public function setReturnTue(?bool $returnTue): self
    {
        $this->returnTue = $returnTue;
        
        return $this;
    }
    
    public function getReturnTueTime(): ?string
    {
        return $this->returnTueTime;
    }
    
    public function setReturnTueTime(?string $returnTueTime): self
    {
        $this->returnTueTime = $returnTueTime;
        
        return $this;
    }

    public function getReturnTueMargin(): ?int
    {
        return $this->returnTueMargin;
    }
    
    public function setReturnTueMargin(?string $returnTueMargin): self
    {
        $this->oreturnTueMargin = $returnTueMargin;
        
        return $this;
    }

    public function getOutwardWed(): ?bool
    {
        return $this->outwardWed;
    }
    
    public function setOutwardWed(?bool $outwardWed): self
    {
        $this->outwardWed = $outwardWed;
        
        return $this;
    }
    
    public function getOutwardWedTime(): ?string
    {
        return $this->outwardWedTime;
    }
    
    public function setOutwardWedTime(?string $outwardWedTime): self
    {
        $this->outwardWedTime = $outwardWedTime;
        
        return $this;
    }

    public function getOutwardWedMargin(): ?int
    {
        return $this->outwardWedMargin;
    }
    
    public function setOutwardWedMargin(?string $outwardWedMargin): self
    {
        $this->outwardWedMargin = $outwardWedMargin;
        
        return $this;
    }

    public function getReturnWed(): ?bool
    {
        return $this->returnWed;
    }
    
    public function setReturnWed(?bool $returnWed): self
    {
        $this->returnWed = $returnWed;
        
        return $this;
    }
    
    public function getReturnWedTime(): ?string
    {
        return $this->returnWedTime;
    }
    
    public function setReturnWedTime(?string $returnWedTime): self
    {
        $this->returnWedTime = $returnWedTime;
        
        return $this;
    }

    public function getReturnWedMargin(): ?int
    {
        return $this->returnWedMargin;
    }
    
    public function setReturnWedMargin(?string $returnWedMargin): self
    {
        $this->oreturnWedMargin = $returnWedMargin;
        
        return $this;
    }

    public function getOutwardThu(): ?bool
    {
        return $this->outwardThu;
    }
    
    public function setOutwardThu(?bool $outwardThu): self
    {
        $this->outwardThu = $outwardThu;
        
        return $this;
    }
    
    public function getOutwardThuTime(): ?string
    {
        return $this->outwardThuTime;
    }
    
    public function setOutwardThuTime(?string $outwardThuTime): self
    {
        $this->outwardThuTime = $outwardThuTime;
        
        return $this;
    }

    public function getOutwardThuMargin(): ?int
    {
        return $this->outwardThuMargin;
    }
    
    public function setOutwardThuMargin(?string $outwardThuMargin): self
    {
        $this->outwardThuMargin = $outwardThuMargin;
        
        return $this;
    }

    public function getReturnThu(): ?bool
    {
        return $this->returnThu;
    }
    
    public function setReturnThu(?bool $returnThu): self
    {
        $this->returnThu = $returnThu;
        
        return $this;
    }
    
    public function getReturnThuTime(): ?string
    {
        return $this->returnThuTime;
    }
    
    public function setReturnThuTime(?string $returnThuTime): self
    {
        $this->returnThuTime = $returnThuTime;
        
        return $this;
    }

    public function getReturnThuMargin(): ?int
    {
        return $this->returnThuMargin;
    }
    
    public function setReturnThuMargin(?string $returnThuMargin): self
    {
        $this->oreturnThuMargin = $returnThuMargin;
        
        return $this;
    }

    public function getOutwardFri(): ?bool
    {
        return $this->outwardFri;
    }
    
    public function setOutwardFri(?bool $outwardFri): self
    {
        $this->outwardFri = $outwardFri;
        
        return $this;
    }
    
    public function getOutwardFriTime(): ?string
    {
        return $this->outwardFriTime;
    }
    
    public function setOutwardFriTime(?string $outwardFriTime): self
    {
        $this->outwardFriTime = $outwardFriTime;
        
        return $this;
    }

    public function getOutwardFriMargin(): ?int
    {
        return $this->outwardFriMargin;
    }
    
    public function setOutwardFriMargin(?string $outwardFriMargin): self
    {
        $this->outwardFriMargin = $outwardFriMargin;
        
        return $this;
    }

    public function getReturnFri(): ?bool
    {
        return $this->returnFri;
    }
    
    public function setReturnFri(?bool $returnFri): self
    {
        $this->returnFri = $returnFri;
        
        return $this;
    }
    
    public function getReturnFriTime(): ?string
    {
        return $this->returnFriTime;
    }
    
    public function setReturnFriTime(?string $returnFriTime): self
    {
        $this->returnFriTime = $returnFriTime;
        
        return $this;
    }

    public function getReturnFriMargin(): ?int
    {
        return $this->returnFriMargin;
    }
    
    public function setReturnFriMargin(?string $returnFriMargin): self
    {
        $this->oreturnFriMargin = $returnFriMargin;
        
        return $this;
    }

    public function getOutwardSat(): ?bool
    {
        return $this->outwardSat;
    }
    
    public function setOutwardSat(?bool $outwardSat): self
    {
        $this->outwardSat = $outwardSat;
        
        return $this;
    }
    
    public function getOutwardSatTime(): ?string
    {
        return $this->outwardSatTime;
    }
    
    public function setOutwardSatTime(?string $outwardSatTime): self
    {
        $this->outwardSatTime = $outwardSatTime;
        
        return $this;
    }

    public function getOutwardSatMargin(): ?int
    {
        return $this->outwardSatMargin;
    }
    
    public function setOutwardSatMargin(?string $outwardSatMargin): self
    {
        $this->outwardSatMargin = $outwardSatMargin;
        
        return $this;
    }

    public function getReturnSat(): ?bool
    {
        return $this->returnSat;
    }
    
    public function setReturnSat(?bool $returnSat): self
    {
        $this->returnSat = $returnSat;
        
        return $this;
    }
    
    public function getReturnSatTime(): ?string
    {
        return $this->returnSatTime;
    }
    
    public function setReturnSatTime(?string $returnSatTime): self
    {
        $this->returnSatTime = $returnSatTime;
        
        return $this;
    }

    public function getReturnSatMargin(): ?int
    {
        return $this->returnSatMargin;
    }
    
    public function setReturnSatMargin(?string $returnSatMargin): self
    {
        $this->oreturnSatMargin = $returnSatMargin;
        
        return $this;
    }

    public function getOutwardSun(): ?bool
    {
        return $this->outwardSun;
    }
    
    public function setOutwardSun(?bool $outwardSun): self
    {
        $this->outwardSun = $outwardSun;
        
        return $this;
    }
    
    public function getOutwardSunTime(): ?string
    {
        return $this->outwardSunTime;
    }
    
    public function setOutwardSunTime(?string $outwardSunTime): self
    {
        $this->outwardSunTime = $outwardSunTime;
        
        return $this;
    }

    public function getOutwardSunMargin(): ?int
    {
        return $this->outwardSunMargin;
    }
    
    public function setOutwardSunMargin(?string $outwardSunMargin): self
    {
        $this->outwardSunMargin = $outwardSunMargin;
        
        return $this;
    }

    public function getReturnSun(): ?bool
    {
        return $this->returnSun;
    }
    
    public function setReturnSun(?bool $returnSun): self
    {
        $this->returnSun = $returnSun;
        
        return $this;
    }
    
    public function getReturnSunTime(): ?string
    {
        return $this->returnSunTime;
    }
    
    public function setReturnSunTime(?string $returnSunTime): self
    {
        $this->returnSunTime = $returnSunTime;
        
        return $this;
    }

    public function getReturnSunMargin(): ?int
    {
        return $this->returnSunMargin;
    }
    
    public function setReturnSunMargin(?string $returnSunMargin): self
    {
        $this->oreturnSunMargin = $returnSunMargin;
        
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
