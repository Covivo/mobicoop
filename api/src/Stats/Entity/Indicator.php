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

namespace App\Stats\Entity;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * A short statistic indicator
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readStats"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeStats"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Stats"}
 *              }
 *          },
 *          "home"={
 *              "normalization_context"={"groups"={"readStats"}},
 *              "method"="GET",
 *              "path"="/indicators/home",
 *              "swagger_context" = {
 *                  "summary"="Get the statistics indicators used on Home page",
 *                  "tags"={"Stats"},
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Stats"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Indicator
{
    const DEFAULT_ID = "999999999999";

    /**
     * @var int The id of this Indicator
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readStats"})
     */
    private $id;
    
    /**
     * @var string The label of this Indicator
     *
     * @Groups({"readStats"})
     */
    private $label;

    /**
     * @var float The value of this Indicator
     *
     * @Groups({"readStats"})
     */
    private $value;

    /**
     * @var int The rounded integer value of this Indicator
     *
     * @Groups({"readStats"})
     */
    private $roundedIntValue;

    /**
     * @var boolean True if this Indicator is used on Home Page
     */
    private $home;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }
    
    public function getLabel(): ?String
    {
        return $this->label;
    }

    public function setLabel(?String $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setvalue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getRoundedIntValue(): ?int
    {
        return round($this->value, 0);
    }

    public function setRoundedIntValue(?int $roundedIntValue): self
    {
        $this->roundedIntValue = $roundedIntValue;

        return $this;
    }

    public function isHome(): ?bool
    {
        return $this->home;
    }

    public function setHome(?bool $home): self
    {
        $this->home = $home;

        return $this;
    }
}
