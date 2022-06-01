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

namespace App\Solidary\Entity\SolidaryResult;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary Result Transport
 *
 * ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidarySearch"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */

class SolidaryResultTransport
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidarySearch"})
     */
    private $id;

    /**
     * @var int FamilyName and GivenName of the volunteer
     * @Groups({"readSolidarySearch"})
     */
    private $volunteer;

    /**
    * @var string Id of the volunteer
    * @Groups({"readSolidarySearch"})
    */
    private $volunteerId;

    /**
     * @var string Home town of the volunteer
     * @Groups({"readSolidarySearch"})
     */
    private $home;

    /**
     * @var array Schedule of availability of the volunteer
     * @Groups({"readSolidarySearch"})
     */
    private $schedule;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getVolunteer(): ?string
    {
        return $this->volunteer;
    }

    public function setVolunteer(string $volunteer): self
    {
        $this->volunteer = $volunteer;

        return $this;
    }

    public function getVolunteerId(): ?int
    {
        return $this->volunteerId;
    }

    public function setVolunteerId(int $volunteerId): self
    {
        $this->volunteerId = $volunteerId;

        return $this;
    }

    public function getHome(): ?string
    {
        return $this->home;
    }

    public function setHome(string $home): self
    {
        $this->home = $home;

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
}
