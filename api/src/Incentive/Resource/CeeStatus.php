<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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
 */

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Incentive\Entity\CeeLongDistanceStatus;
use App\Incentive\Entity\CeeShortDistanceStatus;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A CEE status.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readIncentive"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeIncentive"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Incentive"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Incentive"},
 *                  "summary"="Not implemented"
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CeeStatus
{
    public const DEFAULT_ID = '999999999999';
    public const LONG_DISTANCE_MINIMUM_IN_METERS = 80000;
    public const LONG_DISTANCE_MINIMUM_PRICE_BY_KM = 0.06;

    /**
     * @var int The id of this CEE Status
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readIncentive"})
     */
    private $id;

    /**
     * @var CeeLongDistanceStatus Long distance journeys status (>= LONG_DISTANCE_MINIMUM_KMS)
     *
     * @Groups({"readIncentive"})
     */
    private $longDistanceStatus;

    /**
     * @var CeeShortDistanceStatus Short distance journeys status (< LONG_DISTANCE_MINIMUM_KMS and >= SHORT_DISTANCE_MIN_PRICE_BY_KM)
     *
     * @Groups({"readIncentive"})
     */
    private $shortDistanceStatus;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
        $this->longDistanceStatus = new CeeLongDistanceStatus();
        $this->shortDistanceStatus = new CeeShortDistanceStatus();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLongDistanceStatus(): ?CeeLongDistanceStatus
    {
        return $this->longDistanceStatus;
    }

    public function setLongDistanceStatus(?CeeLongDistanceStatus $longDistanceStatus): self
    {
        $this->longDistanceStatus = $longDistanceStatus;

        return $this;
    }

    public function getShortDistanceStatus(): ?CeeShortDistanceStatus
    {
        return $this->shortDistanceStatus;
    }

    public function setShortDistanceStatus(?CeeShortDistanceStatus $shortDistanceStatus): self
    {
        $this->shortDistanceStatus = $shortDistanceStatus;

        return $this;
    }
}
