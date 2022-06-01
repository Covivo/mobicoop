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

namespace App\Solidary\Entity\SolidaryVolunteerPlanning;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A solidary volunteer planning
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidaryVolunteerPlanning"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryVolunteerPlanning"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('solidary_transporters_schedule',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryVolunteerPlanning
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidaryVolunteerPlanning"})
     */
    private $id;

    /**
     * @var \DateTimeInterface Date of the planning
     * @Groups({"readSolidaryVolunteerPlanning"})
     */
    private $date;

    /**
     * @var SolidaryVolunteerPlanningItem Details of the morning slot of the planning
     * @Groups({"readSolidaryVolunteerPlanning"})
     */
    private $morningSlot;

    /**
     * @var SolidaryVolunteerPlanningItem Details of the afternoon slot of the planning
     * @Groups({"readSolidaryVolunteerPlanning"})
     */
    private $afternoonSlot;

    /**
     * @var SolidaryVolunteerPlanningItem Details of the evening slot of the planning
     * @Groups({"readSolidaryVolunteerPlanning"})
     */
    private $eveningSlot;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setMorningSlot(SolidaryVolunteerPlanningItem $morningSlot): self
    {
        $this->morningSlot = $morningSlot;

        return $this;
    }

    public function getMorningSlot(): ?SolidaryVolunteerPlanningItem
    {
        return $this->morningSlot;
    }

    public function setAfternoonSlot(SolidaryVolunteerPlanningItem $afternoonSlot): self
    {
        $this->afternoonSlot = $afternoonSlot;

        return $this;
    }

    public function getAfternoonSlot(): ?SolidaryVolunteerPlanningItem
    {
        return $this->afternoonSlot;
    }

    public function setEveningSlot(SolidaryVolunteerPlanningItem $eveningSlot): self
    {
        $this->eveningSlot = $eveningSlot;

        return $this;
    }

    public function getEveningSlot(): ?SolidaryVolunteerPlanningItem
    {
        return $this->eveningSlot;
    }
}
