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
            'frequencyResult'   => $this->getFrequencyResult()
        ];
    }
}
