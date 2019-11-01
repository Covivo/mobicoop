<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use App\User\Entity\User;
use App\Geography\Entity\Address;

/**
 * Carpooling : result resource for a search / ad post.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class Result
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this result.
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var ResultRole|null The result with the carpooler as driver and the person who search / post as a passenger.
     * @Groups("results")
     */
    private $resultDriver;

    /**
     * @var ResultRole|null The result with the carpooler as passenger and the person who search / post as a driver.
     * @Groups("results")
     */
    private $resultPassenger;

    /**
     * @var User The carpooler found.
     * @Groups("results")
     */
    private $carpooler;

    /**
     * @var int The frequency of the search/ad (1 = punctual / 2 = regular).
     * @Groups("results")
     */
    private $frequency;

    /**
     * @var int The frequency of the matching proposal result (1 = punctual / 2 = regular).
     * @Groups("results")
     */
    private $frequencyResult;

    /**
     * @var Address The origin address to display.
     * @Groups("results")
     */
    private $origin;

    /**
     * @var boolean True if the origin is the first waypoint of the journey.
     * @Groups("results")
     */
    private $originFirst;

    /**
     * @var Address The destination address to display.
     * @Groups("results")
     */
    private $destination;

    /**
     * @var boolean True if the destination is the last point of the journey.
     * @Groups("results")
     */
    private $destinationLast;

    /**
     * @var \DateTimeInterface|null The date to display.
     * @Groups("results")
     */
    private $date;

    /**
     * @var \DateTimeInterface|null The time to display.
     * @Groups("results")
     */
    private $time;

    /**
     * @var \DateTimeInterface|null The possible start date if regular.
     * @Groups("results")
     */
    private $startDate;

    /**
     * @var int The number of places offered / requested to display.
     * @Groups("results")
     */
    private $seats;

    /**
     * @var string The computed price to display.
     * @Groups("results")
     */
    private $price;

    /**
     * @var string The comment to display.
     * @Groups("results")
     */
    private $comment;

    /**
     * @var boolean|null The journey is available on mondays (if regular).
     * @Groups("results")
     */
    private $monCheck;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     * @Groups("results")
     */
    private $tueCheck;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     * @Groups("results")
     */
    private $wedCheck;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     * @Groups("results")
     */
    private $thuCheck;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     * @Groups("results")
     */
    private $friCheck;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     * @Groups("results")
     */
    private $satCheck;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     * @Groups("results")
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null The outward time to display (if regular and unique).
     * @Groups("results")
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time to display (if regular and unique).
     * @Groups("results")
     */
    private $returnTime;

    /**
     * @var boolean|null The journey has a return trip.
     * @Groups("results")
     */
    private $return;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResultDriver(): ?ResultRole
    {
        return $this->resultDriver;
    }

    public function setResultDriver(?ResultRole $resultDriver): self
    {
        $this->resultDriver = $resultDriver;

        return $this;
    }

    public function getResultPassenger(): ?ResultRole
    {
        return $this->resultPassenger;
    }

    public function setResultPassenger(?ResultRole $resultPassenger): self
    {
        $this->resultPassenger = $resultPassenger;

        return $this;
    }

    public function getCarpooler(): ?User
    {
        return $this->carpooler;
    }

    public function setCarpooler(?User $carpooler): self
    {
        $this->carpooler = $carpooler;

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

    public function getFrequencyResult(): ?int
    {
        return $this->frequencyResult;
    }

    public function setFrequencyResult(int $frequencyResult): self
    {
        $this->frequencyResult = $frequencyResult;

        return $this;
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

    public function isOriginFirst(): ?bool
    {
        return $this->originFirst;
    }
    
    public function setOriginFirst(bool $isOriginFirst): self
    {
        $this->originFirst = $isOriginFirst;
        
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

    public function isDestinationLast(): ?bool
    {
        return $this->destinationLast;
    }
    
    public function setDestinationLast(bool $isDestinationLast): self
    {
        $this->destinationLast = $isDestinationLast;
        
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(?\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }
    
    public function setPrice(?string $price)
    {
        $this->price = $price;
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

    public function isMonCheck(): ?bool
    {
        return $this->monCheck;
    }

    public function setMonCheck(?bool $monCheck): self
    {
        $this->monCheck = $monCheck;

        return $this;
    }

    public function isTueCheck(): ?bool
    {
        return $this->tueCheck;
    }

    public function setTueCheck(?bool $tueCheck): self
    {
        $this->tueCheck = $tueCheck;

        return $this;
    }

    public function isWedCheck(): ?bool
    {
        return $this->wedCheck;
    }

    public function setWedCheck(?bool $wedCheck): self
    {
        $this->wedCheck = $wedCheck;

        return $this;
    }

    public function isThuCheck(): ?bool
    {
        return $this->thuCheck;
    }

    public function setThuCheck(?bool $thuCheck): self
    {
        $this->thuCheck = $thuCheck;

        return $this;
    }

    public function isFriCheck(): ?bool
    {
        return $this->friCheck;
    }

    public function setFriCheck(?bool $friCheck): self
    {
        $this->friCheck = $friCheck;

        return $this;
    }

    public function isSatCheck(): ?bool
    {
        return $this->satCheck;
    }

    public function setSatCheck(?bool $satCheck): self
    {
        $this->satCheck = $satCheck;

        return $this;
    }

    public function isSunCheck(): ?bool
    {
        return $this->sunCheck;
    }

    public function setSunCheck(?bool $sunCheck): self
    {
        $this->sunCheck = $sunCheck;

        return $this;
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?\DateTimeInterface $outwardTime): self
    {
        $this->outwardTime = $outwardTime;

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?\DateTimeInterface $returnTime): self
    {
        $this->returnTime = $returnTime;

        return $this;
    }

    public function hasReturn(): ?bool
    {
        return $this->return;
    }
    
    public function setReturn(bool $hasReturn): self
    {
        $this->return = $hasReturn;
        
        return $this;
    }
}
