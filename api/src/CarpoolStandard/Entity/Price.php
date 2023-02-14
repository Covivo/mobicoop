<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\CarpoolStandard\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A price.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class Price
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this price
     *
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var string either « FREE », « PAYING » or « UNKNOWN »; « UNKNOWN » is given when it should be « PAYING » but we cannot set the price yet
     *
     * @Assert\NotBlank
     * @Groups({"read", "write"})
     */
    private $type;

    /**
     * @var null|float carpooling passenger cost estimate; In the case of integrated booking by API, amount expected by the carpooling operator
     *
     * @Groups({"read", "write"})
     */
    private $amount;

    /**
     * @var null|string ISO 4217 code representing the currency of the price
     *
     * @Groups({"read", "write"})
     */
    private $currency;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}
