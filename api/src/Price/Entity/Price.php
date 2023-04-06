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
 */

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
     * @var int the id of this contact
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"read", "write"})
     */
    private $id;

    /**
     * The value of the price.
     *
     * @var float
     *
     * @Groups({"read", "write"})
     */
    private $value;

    /**
     * The type of the price.
     *
     * @var string
     *
     * @Groups({"read", "write"})
     */
    private $type;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): Price
    {
        $this->id = $id;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): Price
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Price
    {
        $this->type = $type;

        return $this;
    }
}
