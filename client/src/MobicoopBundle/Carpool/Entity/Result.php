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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * Carpooling : result resource for a search / ad post.
 */
class Result implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this result.
     */
    private $id;

    /**
     * @var string|null The iri of this result.
     */
    private $iri;

    /**
     * @var ResultRole|null The result with the carpooler as driver and the person who search / post as a passenger.
     */
    private $resultDriver;

    /**
     * @var ResultRole|null The result with the carpooler as passenger and the person who search / post as a driver.
     */
    private $resultPassenger;

    /**
     * @var User The carpooler found.
     */
    private $carpooler;

    /**
     * @var int The frequency of the search/ad (1 = punctual / 2 = regular).
     */
    private $frequency;

    /**
     * @var int The frequency of the matching proposal result (1 = punctual / 2 = regular).
     */
    private $frequencyResult;

    /**
     * @var Address The origin address to display for the result.
     */
    private $origin;

    /**
     * @var boolean True if the origin is the first waypoint of the journey.
     */
    private $originFirst;

    /**
     * @var Address The destination address to display for the result.
     */
    private $destination;

    /**
     * @var boolean True if the destination is the last point of the journey.
     */
    private $destinationLast;

    /**
     * @var \DateTimeInterface|null The date to display for the result.
     */
    private $date;

    /**
     * @var \DateTimeInterface|null The time to display for the result.
     */
    private $time;

    /**
     * @var int The number of places offered / requested to display.
     */
    private $seats;

    /**
     * @var string The computed price to display.
     */
    private $price;

    /**
     * @var string The comment to display.
     */
    private $comment;

    /**
     * @var boolean|null The journey is available on mondays (if regular).
     */
    private $monCheck;

    /**
     * @var boolean|null The journey is available on tuesdays (if regular).
     */
    private $tueCheck;

    /**
     * @var boolean|null The journey is available on wednesdays (if regular).
     */
    private $wedCheck;

    /**
     * @var boolean|null The journey is available on thursdays (if regular).
     */
    private $thuCheck;

    /**
     * @var boolean|null The journey is available on fridays (if regular).
     */
    private $friCheck;

    /**
     * @var boolean|null The journey is available on saturdays (if regular).
     */
    private $satCheck;

    /**
     * @var boolean|null The journey is available on sundays (if regular).
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null The outward time to display (if regular and unique).
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time to display (if regular and unique).
     */
    private $returnTime;

    /**
     * @var boolean|null The journey has a return trip.
     */
    private $return;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/results/".$id);
        }
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

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

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

    // If you want more info you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'resultDriver'      => $this->getResultdriver(),
            'resultPassenger'   => $this->getResultPassenger(),
            'carpooler'         => $this->getCarpooler(),
            'frequency'         => $this->getFrequency(),
            'frequencyResult'   => $this->getFrequencyResult(),
            'origin'            => $this->getOrigin(),
            'destination'       => $this->getDestination(),
            'date'              => $this->getDate(),
            'time'              => $this->getTime(),
            'seats'             => $this->getSeats(),
            'price'             => $this->getPrice(),
            'comment'           => $this->getComment(),
            'isMonCheck'        => $this->isMonCheck(),
            'isTueCheck'        => $this->isTueCheck(),
            'isWedCheck'        => $this->isWedCheck(),
            'isThuCheck'        => $this->isThuCheck(),
            'isFriCheck'        => $this->isFriCheck(),
            'isSatCheck'        => $this->isSatCheck(),
            'isSunCheck'        => $this->isSunCheck(),
            'outwardTime'       => $this->getOutwardTime(),
            'returnTime'        => $this->getReturnTime(),
            'return'            => $this->hasReturn()
        ];
    }
}
