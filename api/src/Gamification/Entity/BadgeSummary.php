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

namespace App\Gamification\Entity;

use App\Gamification\Entity\Badge;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Gamification : The Badge progress summary
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgeSummary
{
    /**
     * @var int Badge's id
     * @Groups({"readGamification"})
     */
    private $badgeId;

    /**
     * @var string Badge's name
     * @Groups({"readGamification"})
     */
    private $badgeName;

    /**
     * @var string Badge's title
     * @Groups({"readGamification"})
     */
    private $badgeTitle;

    /**
     * @var SequenceStatus[] Badge's sequence status
     * @Groups({"readGamification"})
     */
    private $sequences;

    /**
     * @var string Badge's icon
     * @Groups({"readGamification"})
     */
    private $icon;

    /**
     * @var string Badge's deteriorated icon
     * @Groups({"readGamification"})
     */
    private $decoratedIcon;

    /**
     * @var string Badge's image (when we earn the badge)
     * @Groups({"readGamification"})
     */
    private $image;

    /**
     * @var string Badge's image light version (when we earn the badge)
     * @Groups({"readGamification"})
     */
    private $imageLight;

    public function getBadgeId(): ?int
    {
        return $this->badgeId;
    }

    public function setBadgeId(int $badgeId): self
    {
        $this->badgeId = $badgeId;

        return $this;
    }

    public function getBadgeName(): ?string
    {
        return $this->badgeName;
    }

    public function setBadgeName(string $badgeName): self
    {
        $this->badgeName = $badgeName;

        return $this;
    }

    public function getBadgeTitle(): ?string
    {
        return $this->badgeTitle;
    }

    public function setBadgeTitle(string $badgeTitle): self
    {
        $this->badgeTitle = $badgeTitle;

        return $this;
    }

    public function getSequences(): ?array
    {
        return $this->sequences;
    }

    public function setSequences(array $sequences): self
    {
        $this->sequences = $sequences;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getDecoratedIcon(): ?string
    {
        return $this->decoratedIcon;
    }

    public function setDecoratedIcon(?string $decoratedIcon): self
    {
        $this->decoratedIcon = $decoratedIcon;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageLight(): ?string
    {
        return $this->imageLight;
    }

    public function setImageLight(?string $imageLight): self
    {
        $this->imageLight = $imageLight;

        return $this;
    }
}
