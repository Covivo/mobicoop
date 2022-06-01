<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\RdexPlus\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * RDEX+ : A Price
 * Documentation : https://rdex.fabmob.io/
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Price
{
    public const TYPE_FREE = "free";
    public const TYPE_FIXED = "fixed";
    public const TYPE_VARIABLE = "variable";
    public const TYPE_UNKNOWN = "unknown";
    public const VALID_TYPES = [
        self::TYPE_FREE,
        self::TYPE_FIXED,
        self::TYPE_VARIABLE,
        self::TYPE_UNKNOWN
    ];

    /**
     * @var float Journey's amount Required if type=fixed or variable
     * @Assert\NotBlank
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $amount;

    /**
     * @var string Journey's price type. (free, fixed, variable, unknown)
     * nnknown must be returned when the journey is free, but the amount is unknown (and price.amount must then be left empty).
     * @Assert\NotBlank
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $type;

    /**
     * @var string Kilometric price of the journey. Required if price.type=variable.
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $kilometricPrice;

    /**
     * @var string Journey's money currency
     *
     * @Groups({"rdexPlusRead","rdexPlusWrite"})
     */
    private $currency;


    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isKilometricPrice(): ?string
    {
        return $this->kilometricPrice;
    }

    public function setKilometricPrice(?string $kilometricPrice): self
    {
        $this->kilometricPrice = $kilometricPrice;

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
