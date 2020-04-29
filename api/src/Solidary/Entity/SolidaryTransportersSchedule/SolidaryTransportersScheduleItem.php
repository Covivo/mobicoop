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

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A solidary transporter planning item
 */
class SolidaryTransportersScheduleItem
{
    /**
     * @var string First name and family name of the volunteer designated by this item
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
    private $solidaryId;
    
    /**
     * @var int Id of the SolidarySolution designated by this item
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $solidarySolutionId;

    /**
     * @var int Status of this item (status of the SolidaryAsk)
     * @Groups({"readSolidaryTransportersSchedule"})
     */
    private $status;

    public function getVolunteer(): ?string
    {
        return $this->volunteer;
    }

    public function setVolunteer(string $volunteer): self
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

    public function getSolidaryId(): ?int
    {
        return $this->solidaryId;
    }

    public function setSolidaryId(int $solidaryId): self
    {
        $this->solidaryId = $solidaryId;
        
        return $this;
    }

    public function getSolidarySolutionId(): ?int
    {
        return $this->solidarySolutionId;
    }

    public function setSolidarySolutionId(int $solidarySolutionId): self
    {
        $this->solidarySolutionId = $solidarySolutionId;
        
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
