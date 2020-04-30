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

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary transporters planning
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidaryTransportersSchedule"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryTransportersSchedule"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_transporters_schedule',object)"
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)"
 *          },
 *          "put"={
 *             "security"="is_granted('reject',object)"
 *          },
 *          "delete"={
 *             "security"="is_granted('reject',object)"
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryTransportersSchedule
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $id;

    /**
     * @var \DateTimeInterface Start date of the planning
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface End date of the planning
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $endDate;

    /**
     * @var array Array of SolidaryTransportersScheduleItem
     * @Groups({"readSolidaryTransportersSchedule","writeSolidaryTransportersSchedule"})
     */
    private $schedule;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
        $this->schedule = [];
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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
