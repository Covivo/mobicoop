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

namespace App\Solidary\Entity\SolidaryTransporterPlanning;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Communication\Entity\Medium;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary transporter planning
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidaryTransporterPlanning"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryTransporterPlanning"}}
 *      },
 *      collectionOperations={
 *          "get","post"
 *
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 */
class SolidaryTransporterPlanning
{
    /**
     * @var \DateTimeInterface Start date of the planning
     */
    private $startDate;

    /**
     * @var \DateTimeInterface End date of the planning
     */
    private $endDate;

    /**
     * @var array Array of SolidaryTransporterPlanningItem
     */
    private $schedule;
}
