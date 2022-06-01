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

namespace App\Solidary\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A solidary asks list item
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAsksListItem
{
    public const DRIVER_TYPE_VOLUNTEER = 0;
    public const DRIVER_TYPE_CARPOOLER = 1;

    /**
     * @var int The frequency (1 = punctual; 2 = regular).
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $frequency;

    /**
     * @var int Ask status
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $status;

    /**
    * @var \DateTimeInterface The start date for regular, the date for punctual
     * @Groups({"readSolidary","writeSolidary"})
    */
    private $fromDate;

    /**
    * @var \DateTimeInterface The end date for regular, null for punctual
     * @Groups({"readSolidary","writeSolidary"})
    */
    private $toDate;

    /**
     * @var array array of schedules to display
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $schedule;

    /**
    * @var string The firstname + familyname of the volunteer/carpooler driver
    * @Groups({"readSolidary","writeSolidary"})
    */
    private $driver;

    /**
     * @var string Phone number of the driver
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $telephone;

    /**
     * @var int Driver's Type (0 : Volunteer, 1 : Carpooler)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $driverType;

    /**
     * @var int Id of the Solidary Solution for this Ask item (usefull for a SolidaryContact)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $solidarySolutionId;

    /**
     * @var array Messages of the Solidary Solution for this Ask item (usefull for a SolidaryContact)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $messages;

    public function __construct()
    {
        $this->messages = [];
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getSchedule(): ?array
    {
        return $this->schedule;
    }

    public function setSchedule(array $schedule): self
    {
        $this->schedule = $schedule;

        return $this;
    }

    public function getDriver(): ?string
    {
        return $this->driver;
    }

    public function setDriver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getDriverType(): ?int
    {
        return $this->driverType;
    }

    public function setDriverType(int $driverType): self
    {
        $this->driverType = $driverType;

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

    public function getMessages(): ?array
    {
        return $this->messages;
    }

    public function setMessages(?array $messages): self
    {
        $this->messages = $messages;

        return $this;
    }
}
