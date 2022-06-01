<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Price\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Price\Controller\RoundPrice;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A price.
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *     },
 *     collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Misc"}
 *              }
 *          },
 *          "round"={
 *              "method"="POST",
 *              "path"="/prices/round",
 *              "controller"=RoundPrice::class,
 *              "swagger_context" = {
 *                  "tags"={"Misc"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Misc"}
 *              }
 *          },
 *      }
 * )
 */
class Price
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this contact.
     * @ApiProperty(identifier=true)
     * @Groups({"read", "write"})
     */
    private $id;

    /**
     * The value of the price
     *
     * @var float
     * @Groups({"read", "write"})
     */
    private $value;

    /**
     * The frequency of the ad
     *
     * @var int
     * @Groups({"read", "write"})
     */
    private $frequency;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Price
     */
    public function setId(int $id): Price
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Price
     */
    public function setValue(float $value): Price
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
    }

    /**
     * @param int $frequency
     * @return Price
     */
    public function setFrequency(int $frequency): Price
    {
        $this->frequency = $frequency;
        return $this;
    }
}
