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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Matrix Carpool after a Matching diagnostic
 */
class MassMatrix
{

    /**
     * @var array of MassJourneys
     */
    private $originalsJourneys;

    /**
     * @var array of MassCarpools
     */
    private $carpools;

    /**
     * @var int Saved distance
     */
    private $savedDistance;

    /**
     * @var int Saved duration
     */
    private $savedDuration;

    /**
     * @var float Saved CO2
     */
    private $savedCO2;

    /**
     * MassMatrix constructor.
     */
    public function __construct()
    {
        $this->originalsJourneys = new ArrayCollection();
        $this->carpools = new ArrayCollection();
    }


    public function getOriginalsJourneys(): Collection
    {
        return $this->originalsJourneys;
    }

    public function addOriginalsJourneys(MassJourney $journey)
    {
        if (!$this->originalsJourneys->contains($journey)) {
            $this->originalsJourneys->add($journey);
        }

        return $this;
    }

    public function getCarpools(): Collection
    {
        return $this->carpools;
    }

    public function addCarpools(MassCarpool $carpool)
    {
        if (!$this->carpools->contains($carpool)) {
            $this->carpools->add($carpool);
        }

        return $this;
    }

    public function getSavedDistance(): int
    {
        return $this->savedDistance;
    }

    public function setSavedDistance(int $savedDistance)
    {
        $this->savedDistance = $savedDistance;
    }

    public function getSavedDuration(): int
    {
        return $this->savedDuration;
    }

    public function setSavedDuration(int $savedDuration)
    {
        $this->savedDuration = $savedDuration;
    }

    public function getSavedCO2(): float
    {
        return $this->savedCO2;
    }

    public function setSavedCO2(float $savedCO2)
    {
        $this->savedCO2 = $savedCO2;
    }


    /**
     * Return the MassJourney of the person by idPerson
     * @param int $idPerson
     * @return MassJourney|null
     */
    public function getJourneyOfAPerson(int $idPerson): MassJourney
    {
        foreach ($this->originalsJourneys as $journey) {
            if ($journey->getidPerson()==$idPerson) {
                return $journey;
            }
        }
        return null;
    }

    /**
     * Return the MassCarpool between two Persons
     * @param int $idPerson1
     * @param int $idPerson2
     * @return MassCarpool|null
     */
    public function getCarpoolBetweenTwoPersons(int $idPerson1, int $idPerson2) : MassCarpool
    {
        foreach ($this->carpools as $carpool) {
            if ($carpool->getidPerson1()==$idPerson1 && $carpool->getidPerson2()==$idPerson2) {
                return $carpool;
            }
        }
        return null;
    }

    /**
     * Return the MassCarpools of a Person
     * @param int $idPerson
     * @return Collection
     */
    public function getCarpoolsOfAPerson(int $idPerson) : Collection
    {
        $carpools = new ArrayCollection();

        foreach ($this->carpools as $carpool) {
            if (($carpool->getidPerson1()==$idPerson || $carpool->getidPerson2()==$idPerson) && !$carpools->contains($carpool)) {
                $carpools->add($carpool);
            }
        }
        return $carpools;
    }
}
