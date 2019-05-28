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

namespace Mobicoop\Bundle\MobicoopBundle\Match\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Resource;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Direction;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Mass.
 */
class MassPerson implements Resource
{
    /**
     * @var int The id of this event.
     */
    private $id;

    /**
     * @var string|null The iri of this event.
     */
    private $iri;

    /**
     * @var string|null The given id of the person.
     */
    private $givenId;

    /**
     * @var string|null The first name of the person.
     */
    private $givenName;

    /**
     * @var string|null The family name of the person.
     */
    private $familyName;

    /**
     * @var Address The personal address of the person.
     */
    private $personalAddress;

    /**
     * @var Address The work address of the person.
     */
    private $workAddress;

    /**
     * @var Mass The original mass file of the person.
     */
    private $mass;

    /**
     * @var Direction|null The direction between the personal address and the work address.
     */
    private $direction;

    /**
     * @var \DateTimeInterface|null The outward time.
     */
    private $outwardTime;

    /**
     * @var \DateTimeInterface|null The return time.
     */
    private $returnTime;

    /**
     * @var ArrayCollection|null The potential matchings if the person is driver.
     */
    private $matchingsAsDriver;

    /**
     * @var ArrayCollection|null The potential matchings if the person is passenger.
     */
    private $matchingsAsPassenger;


    public function __construct($id = null)
    {
        $this->id = $id;
        $this->matchingsAsDriver = new ArrayCollection();
        $this->matchingsAsPassenger = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
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

    public function getGivenId(): string
    {
        return $this->givenId;
    }

    public function setGivenId(string $givenId): self
    {
        $this->givenId = $givenId;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;
        if ($this->givenName == '') {
            $this->givenName = null;
        }
        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName(?string $familyName): self
    {
        $this->familyName = $familyName;
        if ($this->familyName == '') {
            $this->familyName = null;
        }
        return $this;
    }

    public function getPersonalAddress(): Address
    {
        return $this->personalAddress;
    }

    public function setPersonalAddress(Address $address): self
    {
        $this->personalAddress = $address;

        return $this;
    }

    public function getWorkAddress(): Address
    {
        return $this->workAddress;
    }

    public function setWorkAddress(Address $address): self
    {
        $this->workAddress = $address;

        return $this;
    }

    public function getMass(): Mass
    {
        return $this->mass;
    }

    public function setMass(?Mass $mass): self
    {
        $this->mass = $mass;

        return $this;
    }

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function setDirection(?Direction $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getOutwardTime(): ?\DateTimeInterface
    {
        return $this->outwardTime;
    }

    public function setOutwardTime(?string $outwardTime): self
    {
        if ($outwardTime) {
            $this->outwardTime = \Datetime::createFromFormat('H:i:s', $outwardTime);
        }

        return $this;
    }

    public function getReturnTime(): ?\DateTimeInterface
    {
        return $this->returnTime;
    }

    public function setReturnTime(?string $returnTime): self
    {
        if ($returnTime) {
            $this->returnTime = \Datetime::createFromFormat('H:i:s', $returnTime);
        }

        return $this;
    }

    public function getMatchingsAsDriver()
    {
        return $this->matchingsAsDriver->getValues();
    }

    public function addMatchingsAsDriver(Direction $matchingsAsDriver): self
    {
        if (!$this->matchingsAsDriver->contains($matchingsAsDriver)) {
            $this->matchingsAsDriver->add($matchingsAsDriver);
        }

        return $this;
    }

    public function getMatchingsAsPassenger()
    {
        return $this->matchingsAsPassenger->getValues();
    }

    public function addMatchingsAsPassenger(Direction $matchingsAsPassenger): self
    {
        if (!$this->matchingsAsPassenger->contains($matchingsAsPassenger)) {
            $this->matchingsAsPassenger->add($matchingsAsPassenger);
        }

        return $this;
    }
}
