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

namespace App\Gamification\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Gamification\Entity\BadgeProgression;

/**
 * Gamification : The badges board of a User
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readGamification"}, "enable_max_depth"="true"}
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('badges_board',object)",
 *              "swagger_context" = {
 *                  "summary"="Get the badges board of the User who make the call",
 *                  "tags"={"Gamification"}
 *               }
 *           }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "summary"="Not implemented",
 *                  "tags"={"Gamification"}
 *              }
 *          }
 *      }
 * )
 *  @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgesBoard
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this BadgesBoard.
     * @Assert\NotBlank
     * @Groups({"readGamification"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var BadgeProgression[] The Badges progressions of this BadgesBoard (all the badges active on the platform)
     * @Groups({"readGamification"})
     */
    private $badges;

    /**
     * @var bool If the User owning this board accept the gamification tracking
     * @Groups({"readGamification"})
     */
    private $acceptGamification;

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

    public function getBadges(): ?array
    {
        return $this->badges;
    }

    public function setBadges(array $badges): self
    {
        $this->badges = $badges;

        return $this;
    }

    public function hasAcceptGamification(): ?bool
    {
        return $this->acceptGamification;
    }

    public function setAcceptGamification(bool $acceptGamification): self
    {
        $this->acceptGamification = $acceptGamification;

        return $this;
    }
}
