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

namespace App\Payment\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A payment week (for regular carpools).
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"}
 *     },
 *     collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "read"="false",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 *  @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PaymentWeek
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this payment week.
     * @Groups({"readPayment"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string|null The week and year fo this payment week.
     * @Groups({"readPayment"})
     */
    private $week;

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

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getWeek(): ?string
    {
        return $this->week;
    }

    public function setWeek(?string $week): self
    {
        $this->week = $week;

        return $this;
    }
}
