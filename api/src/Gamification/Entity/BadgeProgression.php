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

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Gamification : The current progression of a Badge
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgeProgression
{
    /**
     * @var BadgeSummary The BadgeSummary of this BadgeProgression
     * @Groups({"readGamification"})
     */
    private $badgeSummary;

    /**
     * @var bool If the Badge is earned
     * @Groups({"readGamification"})
     */
    private $earned;

    public function getBadge(): ?BadgeSummary
    {
        return $this->badgeSummary;
    }

    public function setBadge(BadgeSummary $badgeSummary): self
    {
        $this->badgeSummary = $badgeSummary;

        return $this;
    }
    
    public function isEarned(): ?bool
    {
        return $this->earned;
    }

    public function setEarned(bool $earned): self
    {
        $this->earned = $earned;

        return $this;
    }
}
