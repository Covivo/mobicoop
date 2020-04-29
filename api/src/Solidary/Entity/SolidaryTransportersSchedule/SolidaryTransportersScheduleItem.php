<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Entity\SolidaryTransportersSchedule;

use App\Solidary\Entity\SolidaryUser;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A solidary transporter planning item
 */
class SolidaryTransportersScheduleItem
{
    /**
     * @var SolidaryUser First name and family name of the volunteer designated by this item
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $volunteer;

    /**
     * @var \DateTimeInterface Date of this item
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $date;

    /**
     * @var string Hour slot of this item (m : morning, a : afternoon, e : evening)
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $slot;

    /**
     * @var int Id of the Solidary designated by this item
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $idSolidary;
    
    /**
     * @var int Id of the SolidarySolution designated by this item
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $idSolidarySolution;

    /**
     * @var int Status of this item (status of the SolidaryAsk)
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $status;

    public function getVolunteer(): ?SolidaryUser
    {
        return $this->volunteer;
    }

    public function setVolunteer(SolidaryUser $volunteer): self
    {
        $this->volunteer = $volunteer;

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
    
    public function getSlot(): ?string
    {
        return $this->slot;
    }

    public function setSlot(string $slot): self
    {
        $this->slot = $slot;

        return $this;
    }

    public function getIdSolidary(): ?int
    {
        return $this->idSolidary;
    }

    public function setIdSolidary(int $idSolidary): self
    {
        $this->idSolidary = $idSolidary;
        
        return $this;
    }

    public function getIdSolidarySolution(): ?int
    {
        return $this->idSolidarySolution;
    }

    public function setIdSolidarySolution(int $idSolidarySolution): self
    {
        $this->idSolidarySolution = $idSolidarySolution;
        
        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        
        return $this;
    }
}
